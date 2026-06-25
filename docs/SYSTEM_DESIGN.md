# System Design

This document is the source of truth for the architecture of Artt, a library management system for art books, paintings, painting materials, and drawing workshops. Update it whenever product scope, data modeling, authorization, or frontend architecture changes.

## Product Summary

Artt manages an artistic library with two main business areas:

- Catalog and stock management for physical artistic resources: art books, paintings, paint, brushes, canvases, easels, pencils, markers, and similar material.
- Workshop management for drawing sessions coordinated between administrators, trainers, and clients.

The platform has three user experiences:

- Public/client front office for browsing the catalog, reserving resources, registering for workshops, managing personal activity, and contacting the library.
- Trainer dashboard for assigned workshops, availability decisions, and participant lists.
- Admin back office for users, roles, catalog, stock, reservations, trainers, workshops, registrations, and contact messages.

## Current Technical Context

- Backend: Laravel 13, based on the installed `laravel/framework` version in `composer.json`.
- Runtime target: PHP 8.4.
- Database: MySQL for development, staging, and production.
- Frontend: React with Inertia.js, Vite, and Tailwind CSS.
- UI component library: PrimeReact for operational interfaces.
- Authentication base: Laravel React starter kit with Inertia authentication scaffolding.
- Authorization: `spatie/laravel-permission` for roles and permissions, plus Laravel policies for model-specific rules.

The original cahier des charges mentions Laravel 12 and Blade. The project uses Laravel 13, MySQL, and React + Inertia through the Laravel React starter kit. This document follows the actual intended stack.

## Design Principles

- Use Laravel and Inertia conventions before adding custom infrastructure.
- Keep controllers thin: authorize, validate, call an action or service, and return an Inertia page, redirect, or JSON response.
- Keep business rules in explicit action classes or services, not inside React components, route closures, or large controllers.
- Model trainers as users with a trainer role and profile data, not as a completely separate authentication identity.
- Use roles for broad access and policies for object-level decisions.
- Treat stock changes as business-critical and keep them transactional.
- Build the first version as a clean monolith. Do not split into services or APIs before there is a real need.
- Prefer clear status workflows over free-text state fields.
- Avoid adding architecture folders before they contain real code.

## Proposed Application Structure

```text
app/
  Actions/
    Reservations/
    Sessions/
    Stock/
  Enums/
  Http/
    Controllers/
      Admin/
      Client/
      Trainer/
    Middleware/
    Requests/
  Models/
  Notifications/
  Policies/
  Services/
database/
  factories/
  migrations/
  seeders/
resources/
  css/
  js/
    Components/
    Layouts/
    Pages/
      Admin/
      Auth/
      Catalog/
      Client/
      Contact/
      Sessions/
      Trainer/
    lib/
    types/
routes/
  web.php
tests/
  Feature/
  Unit/
```

`routes/web.php` remains the primary route file because Inertia uses Laravel web routes. Add `routes/api.php` only when a separate external client needs an API.

## Roles And Permissions

Use Spatie roles:

- `admin`
- `trainer`
- `client`

Recommended permission groups:

- `manage users`
- `manage roles`
- `manage categories`
- `manage products`
- `manage reservations`
- `manage trainers`
- `manage sessions`
- `manage contacts`
- `view trainer dashboard`
- `respond assigned sessions`
- `view assigned participants`
- `reserve products`
- `register sessions`
- `manage own profile`

Do not store a plain `role` column on `users`. Spatie already owns role assignment through its pivot tables. This avoids conflicts when a user needs more than one role.

## Core Domain Model

### Users

`users`

- `id`
- `first_name`
- `last_name`
- `email` unique
- `phone` nullable
- `password`
- `email_verified_at` nullable
- `remember_token`
- timestamps

Use `first_name` and `last_name` instead of mixing French column names with English Laravel conventions. Display labels can stay French in the UI.

### Trainer Profiles

`trainer_profiles`

- `id`
- `user_id` foreign key, unique
- `specialty`
- `bio` nullable
- `photo_path` nullable
- `is_active` boolean
- timestamps

Reason: a trainer is not a separate kind of login account. A trainer is a user with the `trainer` role and extra trainer-specific profile fields. This avoids duplicating `nom`, `prenom`, `email`, `telephone`, and `password` across both `users` and `formateurs`.

Practical effect:

- Login, password reset, email verification, and notifications stay in the normal `users` table.
- Role checks use Spatie: `user->hasRole('trainer')`.
- Trainer-only data lives in `trainer_profiles`.
- A trainer can still have client behavior later if needed, because roles are flexible.
- Admins manage trainer profiles by creating or updating a user, assigning the `trainer` role, then filling the trainer profile fields.

### Categories

`categories`

- `id`
- `name`
- `slug` unique
- `description` nullable
- `parent_id` nullable foreign key to `categories.id`
- `type` enum: `product`, `session`, optional if categories later apply to more than products
- `is_active` boolean
- timestamps

Use a parent category instead of hardcoding the tree. This supports:

- Peinture: Acrylique, Huile, Aquarelle, Gouache
- Tableaux: Moderne, Abstrait, Classique, Decoratif
- Livres d'art: Histoire de l'art, Manuels de techniques
- Materiel artistique: Pinceaux, Toiles, Chevalets, Crayons, Feutres

### Products

`products`

- `id`
- `category_id` foreign key
- `reference` unique
- `name`
- `slug` unique
- `description` nullable
- `price` decimal nullable
- `stock_quantity` unsigned integer
- `reserved_quantity` unsigned integer, default 0
- `image_path` nullable
- `status` enum: `draft`, `available`, `unavailable`, `archived`
- timestamps

Available quantity is `stock_quantity - reserved_quantity`. Do not let application code reserve below zero.

### Product Reservations

`reservations`

- `id`
- `user_id` foreign key
- `product_id` foreign key
- `quantity` unsigned integer, default 1
- `reserved_at`
- `pickup_due_at` nullable
- `picked_up_at` nullable
- `returned_at` nullable
- `cancelled_at` nullable
- `status` enum: `pending`, `confirmed`, `cancelled`, `rejected`, `picked_up`, `returned`, `expired`
- timestamps

Improvement over the cahier: "annulee terminee" should not be one status. Cancellation, pickup, and return are different states with different stock effects.

Reservation stock rules:

- `pending`: no stock change by default, unless the product needs temporary holds.
- `confirmed`: increment `reserved_quantity`.
- `cancelled` or `rejected`: release reserved stock if it had been confirmed.
- `picked_up`: keep stock unavailable to other clients.
- `returned`: decrement `reserved_quantity`.
- `expired`: release reserved stock if the client did not pick up the product in time.

All status transitions that touch stock must run inside a database transaction.

### Drawing Sessions

`drawing_sessions`

- `id`
- `trainer_profile_id` foreign key
- `title`
- `slug` unique
- `description` nullable
- `starts_at`
- `ends_at`
- `capacity` unsigned integer
- `registered_count` unsigned integer, default 0
- `price` decimal nullable
- `image_path` nullable
- `status` enum: `draft`, `pending_trainer`, `trainer_refused`, `open`, `full`, `completed`, `cancelled`
- `trainer_response_note` nullable
- `trainer_responded_at` nullable
- timestamps

Use `starts_at` and `ends_at` instead of separate `date`, `heure`, and `duree`. It makes filtering, ordering, calendar views, and conflict checks simpler.

### Session Registrations

`session_registrations`

- `id`
- `user_id` foreign key
- `drawing_session_id` foreign key
- `registered_at`
- `cancelled_at` nullable
- `status` enum: `registered`, `cancelled`, `attended`, `absent`
- timestamps

Add a unique constraint on `user_id` and `drawing_session_id` so a client cannot register twice for the same session.

Registration rules:

- Clients can register only when the session status is `open`.
- When `registered_count` reaches `capacity`, the session becomes `full`.
- If a registration is cancelled before the deadline, release the seat and reopen the session when appropriate.
- Trainers can view participants only for their own assigned sessions.

### Contacts

`contacts`

- `id`
- `name`
- `email`
- `phone` nullable
- `subject`
- `message`
- `status` enum: `new`, `read`, `closed`
- `read_at` nullable
- timestamps

Use timestamps instead of a separate `date_envoi` column.

## Eloquent Relationships

- `User hasOne TrainerProfile`
- `TrainerProfile belongsTo User`
- `TrainerProfile hasMany DrawingSession`
- `Category hasMany Product`
- `Category belongsTo Category as parent`
- `Category hasMany Category as children`
- `Product belongsTo Category`
- `Product hasMany Reservation`
- `Reservation belongsTo Product`
- `Reservation belongsTo User`
- `DrawingSession belongsTo TrainerProfile`
- `DrawingSession hasMany SessionRegistration`
- `SessionRegistration belongsTo DrawingSession`
- `SessionRegistration belongsTo User`
- `User hasMany Reservation`
- `User hasMany SessionRegistration`

## Main Workflows

### Product Reservation

1. Client opens a product page.
2. System checks product status and available quantity.
3. Client creates a reservation request.
4. Admin confirms or rejects the reservation.
5. On confirmation, stock is reserved transactionally.
6. Client can cancel before pickup.
7. Admin marks the product as picked up.
8. Admin marks the product as returned, which releases reserved stock.

### Drawing Session

1. Admin creates a draft session and assigns a trainer.
2. Admin sends it to the trainer, changing status to `pending_trainer`.
3. Trainer confirms or refuses.
4. If confirmed, the session becomes `open` and appears on the front office.
5. Clients register until capacity is reached.
6. At capacity, the session becomes `full`.
7. Trainer views or exports the participant list.
8. Admin or trainer marks attendance after the session.
9. Admin closes the session as `completed`.

### Contact Message

1. Visitor sends a contact message.
2. Admin sees it as `new`.
3. Admin opens it, which can mark it as `read`.
4. Admin closes it after treatment.

## Inertia And React Frontend Design

Use Inertia for server-driven pages with React components:

- Laravel owns routing, validation, authentication, authorization, and persistence.
- React owns interactive page rendering.
- Inertia page props must be intentionally shaped arrays, not raw models with unnecessary data.
- Forms should use Inertia's `useForm`.
- Shared authenticated user data should come from Inertia shared props.
- Server validation errors should be displayed from Inertia form errors.

Recommended frontend structure:

```text
resources/js/
  app.tsx
  Components/
    DataTable.tsx
    EmptyState.tsx
    Form/
    Modal.tsx
    Pagination.tsx
    StatusBadge.tsx
  Layouts/
    PublicLayout.tsx
    AuthenticatedLayout.tsx
    AdminLayout.tsx
    TrainerLayout.tsx
  Pages/
    Home.tsx
    Catalog/Index.tsx
    Catalog/Show.tsx
    Sessions/Index.tsx
    Sessions/Show.tsx
    Client/Dashboard.tsx
    Admin/Dashboard.tsx
    Admin/Products/
    Admin/Reservations/
    Admin/Sessions/
    Trainer/Dashboard.tsx
```

Use TypeScript if possible. It gives better safety for Inertia props and admin tables.

## UI Direction

The UI should feel like a practical cultural institution dashboard, not a marketing landing page.

- Use PrimeReact for operational interfaces: admin, trainer, client account, forms, tables, dialogs, filters, dashboards, date pickers, file uploads, toasts, tabs, and confirmation flows.
- Use custom React and Tailwind composition for public pages: home, catalog, product detail, session listing, session detail, and contact page.
- Public pages may use PrimeReact only for practical controls such as search inputs, dropdown filters, calendars, paginators, buttons, and toast messages.
- Public pages: visual, calm, searchable, and focused on art resources and workshops.
- Admin pages: dense, readable, table-first, with clear filters and quick status actions.
- Trainer pages: schedule-first, with fast confirm/refuse actions and participant access.
- Client account: simple tabs for reservations, workshop registrations, and profile.

Core components to build early:

- App shell layouts for public, client, trainer, and admin areas.
- Table component with search, filters, pagination, empty state, and row actions.
- Status badges for reservations, products, sessions, registrations, and contacts.
- Reusable form fields with Laravel validation error display.
- Image upload preview component.
- Confirmation modal for destructive or state-changing actions.

## Routes And Page Map

Public and client:

- `GET /` home page with new products and upcoming open sessions.
- `GET /catalog` searchable product catalog.
- `GET /catalog/{product:slug}` product detail.
- `POST /products/{product}/reservations` reserve product.
- `GET /sessions` open drawing sessions.
- `GET /sessions/{drawingSession:slug}` session detail.
- `POST /sessions/{drawingSession}/registrations` register for session.
- `GET /account` client dashboard.
- `GET /account/reservations`
- `GET /account/registrations`
- `GET /contact`
- `POST /contact`

Admin:

- `GET /admin` dashboard.
- Resource routes for users, categories, products, reservations, trainers, drawing sessions, contacts.
- Dedicated status routes for reservation confirmation, rejection, pickup, return, and cancellation.
- Dedicated status routes for publishing, cancelling, and completing sessions.

Trainer:

- `GET /trainer` dashboard.
- `GET /trainer/sessions`
- `POST /trainer/sessions/{drawingSession}/confirm`
- `POST /trainer/sessions/{drawingSession}/refuse`
- `GET /trainer/sessions/{drawingSession}/participants`

## Validation And Security Rules

- Every create/update flow uses a form request.
- Every admin, trainer, and client route uses middleware and policies.
- File uploads validate MIME type, size, and dimensions where appropriate.
- Product and session image paths are stored through Laravel's filesystem.
- Admin actions that change stock or capacity must be protected from double-submit issues with transactions and row locks when needed.
- Never trust status values from the browser. Status transitions should be methods or action classes.
- Contact form should include rate limiting.

## Testing Strategy

High-priority feature tests:

- Client can register, log in, and update profile.
- Admin can create category and product.
- Product reservation cannot exceed available quantity.
- Confirming a reservation updates stock correctly.
- Cancelling or returning a reservation releases stock correctly.
- Admin can create a session and assign a trainer.
- Trainer can confirm only assigned sessions.
- Client can register only for open sessions with available seats.
- Session becomes full at capacity.
- Client cannot register twice for the same session.
- Admin can view and close contact messages.

Run before merging meaningful changes:

```bash
composer test
npm run build
```

## Implementation Phases

### Phase 1: Foundation

- Install or align the project with the Laravel React starter kit.
- Configure Spatie roles and permissions.
- Rename user fields to `first_name`, `last_name`, and `phone`.
- Seed roles, permissions, and one admin user.
- Add base layouts for public, client, trainer, and admin.

### Phase 2: Catalog

- Categories with parent/child hierarchy.
- Product CRUD with image upload.
- Public catalog search and filters.
- Product detail page.

### Phase 3: Reservations

- Client reservation request flow.
- Admin reservation management.
- Stock-safe status transitions.
- Client reservation history.

### Phase 4: Trainers And Sessions

- Trainer profiles linked to users.
- Admin session creation and trainer assignment.
- Trainer confirm/refuse workflow.
- Public session listing and registration.
- Participant list and attendance tracking.

### Phase 5: Contact And Dashboard

- Contact form with admin inbox.
- Admin statistics.
- Trainer dashboard.
- Client account dashboard.

### Phase 6: Polish

- Better empty states and loading states.
- Export participant lists.
- Notifications for assigned sessions and reservation decisions.
- Access logs or audit records for important admin actions if time allows.

## Open Decisions

- Reservation hold policy: should stock be held while a reservation is `pending`, or only after admin confirmation?
- Payment: the cahier includes prices, but not online payment. For stage scope, treat prices as informational unless payment is explicitly required.
- Cancellation deadlines for workshops and reservations.
- Whether products can reserve quantities greater than one.
- Whether trainers can edit their own profile photo and specialty or only admins can.
- Whether attendance export should be PDF, CSV, or printable HTML.

## Quality Notes

- Do not copy the exact database from the cahier without adjustment. The separate `formateurs` table and `users.role` column would create duplicated identity and weaker authorization.
- Use English column and class names for code consistency. The UI can be French.
- Keep status names explicit and finite.
- Use transactions for stock and session capacity.
- Do not add a public API until an external client needs it.
