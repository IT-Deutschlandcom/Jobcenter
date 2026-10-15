(() => {
    'use strict';
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finePointer = window.matchMedia('(pointer: fine)').matches;

    // Partikel-Netzwerk im Hintergrund
    function particles() {
        const canvas = document.getElementById('fx-particles');
        if (!canvas || reducedMotion) return;
        const context = canvas.getContext('2d');
        const pointer = {x: -9999, y: -9999};
        const colors = ['245, 194, 27', '61, 123, 255', '155, 92, 255'];
        let width = 0;
        let height = 0;
        let dots = [];
        let frame = 0;
        function resize() {
            const ratio = Math.min(window.devicePixelRatio || 1, 2);
            width = window.innerWidth;
            height = window.innerHeight;
            canvas.width = width * ratio;
            canvas.height = height * ratio;
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            const count = Math.min(90, Math.floor(width * height / 16000));
            dots = Array.from({length: count}, () => ({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - .5) * .35,
                vy: (Math.random() - .5) * .35,
                r: Math.random() * 1.6 + .6,
                c: colors[Math.floor(Math.random() * colors.length)]
            }));
        }
        function draw() {
            context.clearRect(0, 0, width, height);
            for (let i = 0; i < dots.length; i++) {
                const dot = dots[i];
                dot.x += dot.vx;
                dot.y += dot.vy;
                if (dot.x < 0 || dot.x > width) dot.vx *= -1;
                if (dot.y < 0 || dot.y > height) dot.vy *= -1;
                const dx = dot.x - pointer.x;
                const dy = dot.y - pointer.y;
                const distance = Math.hypot(dx, dy);
                if (distance < 120 && distance > 0) {
                    dot.x += dx / distance * 1.2;
                    dot.y += dy / distance * 1.2;
                }
                context.beginPath();
                context.arc(dot.x, dot.y, dot.r, 0, Math.PI * 2);
                context.fillStyle = `rgba(${dot.c}, .8)`;
                context.fill();
                for (let j = i + 1; j < dots.length; j++) {
                    const other = dots[j];
                    const gap = Math.hypot(dot.x - other.x, dot.y - other.y);
                    if (gap < 120) {
                        context.strokeStyle = `rgba(${dot.c}, ${(1 - gap / 120) * .18})`;
                        context.lineWidth = 1;
                        context.beginPath();
                        context.moveTo(dot.x, dot.y);
                        context.lineTo(other.x, other.y);
                        context.stroke();
                    }
                }
            }
            frame = requestAnimationFrame(draw);
        }
        window.addEventListener('resize', resize);
        window.addEventListener('pointermove', event => {
            pointer.x = event.clientX;
            pointer.y = event.clientY;
        }, {passive: true});
        document.addEventListener('visibilitychange', () => {
            cancelAnimationFrame(frame);
            if (!document.hidden) frame = requestAnimationFrame(draw);
        });
        resize();
        draw();
    }

    // Leuchtender Lichtkegel, der dem Mauszeiger folgt
    function cursorGlow() {
        if (reducedMotion || !finePointer) return;
        const glow = document.createElement('div');
        glow.className = 'fx-cursor';
        glow.setAttribute('aria-hidden', 'true');
        document.body.appendChild(glow);
        let x = window.innerWidth / 2;
        let y = window.innerHeight / 2;
        let targetX = x;
        let targetY = y;
        window.addEventListener('pointermove', event => {
            targetX = event.clientX;
            targetY = event.clientY;
        }, {passive: true});
        (function follow() {
            x += (targetX - x) * .12;
            y += (targetY - y) * .12;
            glow.style.transform = `translate3d(${x}px, ${y}px, 0)`;
            requestAnimationFrame(follow);
        })();
    }

    // Karten beim Scrollen gestaffelt einblenden
    function reveal() {
        const items = document.querySelectorAll('.card, .panel, .welcome, .division-card, .member-card');
        if (reducedMotion || !('IntersectionObserver' in window)) return;
        const observer = new IntersectionObserver(entries => {
            let delay = 0;
            for (const entry of entries) {
                if (!entry.isIntersecting) continue;
                entry.target.style.transitionDelay = `${delay}ms`;
                entry.target.classList.add('is-visible');
                delay = Math.min(delay + 70, 420);
                observer.unobserve(entry.target);
                entry.target.addEventListener('transitionend', () => {
                    entry.target.style.transitionDelay = '';
                }, {once: true});
            }
        }, {threshold: .08, rootMargin: '0px 0px -40px 0px'});
        items.forEach(item => {
            item.classList.add('reveal');
            observer.observe(item);
        });
    }

    // Spotlight-Effekt und 3D-Neigung für Karten
    function spotlight() {
        if (!finePointer) return;
        document.querySelectorAll('.card, .panel, .login').forEach(card => {
            const tilt = !reducedMotion && (card.closest('.stats') || card.classList.contains('login'));
            card.addEventListener('pointermove', event => {
                const rect = card.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;
                card.style.setProperty('--mx', `${x}px`);
                card.style.setProperty('--my', `${y}px`);
                if (tilt) {
                    const rotateX = ((y / rect.height) - .5) * -8;
                    const rotateY = ((x / rect.width) - .5) * 8;
                    card.style.transform = `perspective(900px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-4px)`;
                }
            });
            card.addEventListener('pointerleave', () => {
                if (tilt) card.style.transform = '';
            });
        });
    }

    // Ripple-Effekt für Buttons
    function ripple() {
        if (reducedMotion) return;
        document.addEventListener('pointerdown', event => {
            const target = event.target.closest('button, .button');
            if (!target) return;
            const rect = target.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const wave = document.createElement('span');
            wave.className = 'ripple';
            wave.style.width = wave.style.height = `${size}px`;
            wave.style.left = `${event.clientX - rect.left - size / 2}px`;
            wave.style.top = `${event.clientY - rect.top - size / 2}px`;
            target.appendChild(wave);
            wave.addEventListener('animationend', () => wave.remove());
        });
    }

    // Zahlen in den Statistik-Karten hochzählen
    function countUp() {
        if (reducedMotion) return;
        document.querySelectorAll('.stats strong').forEach(element => {
            const text = element.textContent.trim();
            if (!/^\d+$/.test(text)) return;
            const target = parseInt(text, 10);
            if (target === 0) return;
            const duration = 1200;
            const start = performance.now();
            element.textContent = '0';
            (function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                element.textContent = String(Math.round(target * eased));
                if (progress < 1) requestAnimationFrame(step);
            })(start);
        });
    }

    // Scroll-Fortschrittsbalken und "Nach oben"-Button
    function scrollUi() {
        const bar = document.createElement('div');
        bar.className = 'fx-progress';
        bar.setAttribute('aria-hidden', 'true');
        const top = document.createElement('button');
        top.type = 'button';
        top.className = 'fx-top secondary';
        top.setAttribute('aria-label', 'Nach oben');
        top.textContent = '↑';
        top.addEventListener('click', () => window.scrollTo({top: 0, behavior: reducedMotion ? 'auto' : 'smooth'}));
        document.body.append(bar, top);
        function update() {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            const ratio = max > 0 ? window.scrollY / max : 0;
            bar.style.transform = `scaleX(${ratio})`;
            top.classList.toggle('show', window.scrollY > 400);
        }
        window.addEventListener('scroll', update, {passive: true});
        window.addEventListener('resize', update);
        update();
    }

    function init() {
        particles();
        cursorGlow();
        reveal();
        spotlight();
        ripple();
        countUp();
        scrollUi();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
