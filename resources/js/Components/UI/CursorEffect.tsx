import { useEffect, useRef } from 'react';

export default function CursorEffect() {
    const cursorRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const cursor = cursorRef.current;
        if (!cursor || window.matchMedia('(pointer: coarse)').matches) return;

        let x = -100;
        let y = -100;
        let targetX = x;
        let targetY = y;
        let frame = 0;

        const onMove = (event: MouseEvent) => {
            targetX = event.clientX;
            targetY = event.clientY;
            cursor.style.opacity = '1';
        };

        const onEnter = () => cursor.classList.add('is-hot');
        const onLeave = () => cursor.classList.remove('is-hot');

        const tick = () => {
            x += (targetX - x) * 0.18;
            y += (targetY - y) * 0.18;
            cursor.style.left = `${x}px`;
            cursor.style.top = `${y}px`;
            frame = requestAnimationFrame(tick);
        };

        document.addEventListener('mousemove', onMove);
        document.querySelectorAll('a, button, input, select, textarea, [data-cursor-hot]').forEach((element) => {
            element.addEventListener('mouseenter', onEnter);
            element.addEventListener('mouseleave', onLeave);
        });
        frame = requestAnimationFrame(tick);

        return () => {
            cancelAnimationFrame(frame);
            document.removeEventListener('mousemove', onMove);
            document.querySelectorAll('a, button, input, select, textarea, [data-cursor-hot]').forEach((element) => {
                element.removeEventListener('mouseenter', onEnter);
                element.removeEventListener('mouseleave', onLeave);
            });
        };
    }, []);

    return <div ref={cursorRef} className="artt-cursor" aria-hidden="true" />;
}
