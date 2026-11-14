/**
 * Tailwind configuration — MWS theme.
 *
 * Ported from the Play-CDN `tailwind.config` that used to sit inline in the
 * landing page's <head>. Same tokens, now compiled at build time so no CDN is
 * involved and only the utilities actually used ship to the browser.
 *
 * The `premium` additions at the bottom of each block are new: a deeper shadow
 * ramp, shared easing curves and a hairline ring colour, so the "more premium"
 * pass reads as one system rather than a pile of one-off values.
 */

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './*.php',
        './inc/**/*.php',
        './template-parts/**/*.php',
        './src/js/**/*.js',
    ],

    /* Classes that only ever appear from JS (main.js toggles these), so the
       content scanner above can't see them in the markup. */
    safelist: [
        'is-open',
        'aos-ready',
        'overflow-hidden',
        'invisible',
        'opacity-0',
        '-translate-y-2',
        'translate-y-3',
        'bg-emerald-500',
        'bg-ink-muted',
        'is-stuck',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#FEF3F4', 100: '#FCE3E6', 200: '#F7C3C9', 300: '#F09AA4', 400: '#E23B4E',
                    500: '#D31B32', 600: '#C8102E', 700: '#A00C24', 800: '#7A0A1B', 900: '#4A0711',
                },
                ink: { DEFAULT: '#17100F', soft: '#4A3E3C', muted: '#6F625F' },
                dark: { DEFAULT: '#2B0B10', soft: '#3A1219' },
                sand: '#FBF6F4',
                line: '#ECE3E1',
                /* Premium: a single warm metallic used sparingly for accents —
                   hairline rules, eyebrow dots on dark, award frames. */
                gilt: { DEFAULT: '#C9A227', soft: '#E4C868', deep: '#8C6F1A' },
                /* Better Business Bureau blue, for the trust badge only. Not part
                   of the brand palette — it is here so the badge can carry BBB's
                   own colour rather than ours. */
                bbb: { DEFAULT: '#005A78', dark: '#00465E' },
                /* Financing green. Deliberately outside the red brand ramp so the
                   header's two actions never read as the same button — "pay
                   monthly" and "call us" are different asks. Dark enough to hold
                   white text at AA. */
                finance: { DEFAULT: '#12854A', dark: '#0D6A3A' },
            },

            fontFamily: {
                sans: ['"Corben"', 'Georgia', 'ui-serif', 'serif'],
                /* Headings. Anton ships a single 400 weight, so the font-bold
                   utilities on headings are no-ops — the face is already heavy.
                   Applied to h1-h4 in src/css/main.css. */
                display: ['"Anton"', 'Impact', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },

            /* Fluid type scale. Every step interpolates linearly from its minimum at
               a 390px viewport to its maximum at 1440px and holds flat outside that
               range. The rem term is deliberate: a pure-vw clamp ignores the
               reader's browser font size, this one still scales with it.

                 h1 45 → 80px   h2 34 → 54px   h3 20 → 31px   h4 19 → 24px
                 body 15.2 → 16px  small 16.5 → 17.5px  micro 13.5 → 15px
                 eyebrow 13.5 → 14.5px  btn 11.5 → 13.5px  nav 14.5 → 15.5px

               Every step except `body` was scaled up by roughly 12%. Body copy is
               deliberately unchanged — it was already at a comfortable reading
               size, and growing it would have cost line length rather than
               improved legibility.

               Two of these were also broken before: `btn` and (above 1200px)
               `micro` had a rem term large enough that the middle value always
               exceeded the maximum, so clamp pinned them to a fixed size and the
               fluid interpolation never ran. Recomputed from the 390 → 1440
               endpoints, so they now actually scale. */
            fontSize: {
                h1: ['clamp(2.8125rem, 2rem + 3.3333vw, 5rem)', { lineHeight: '1.06' }],
                h2: ['clamp(2.125rem, 1.6607rem + 1.9048vw, 3.375rem)', { lineHeight: '1.12' }],
                h3: ['clamp(1.25rem, 0.9946rem + 1.0476vw, 1.9375rem)', { lineHeight: '1.4' }],
                h4: ['clamp(1.1875rem, 1.0714rem + 0.4762vw, 1.5rem)', { lineHeight: '1.3' }],
                body: ['clamp(0.95rem, 0.9536rem + 0.1905vw, 1rem)', { lineHeight: '1.65' }],
                small: ['clamp(1.0312rem, 1.008rem + 0.0952vw, 1.0938rem)', { lineHeight: '1.55' }],
                micro: ['clamp(0.84375rem, 0.8089rem + 0.1429vw, 0.9375rem)', { lineHeight: '1.45' }],
                eyebrow: ['clamp(0.84375rem, 0.8205rem + 0.0952vw, 0.90625rem)', { lineHeight: '1.2' }],
                btn: ['clamp(0.71875rem, 0.6723rem + 0.1905vw, 0.84375rem)', { lineHeight: '1' }],

                /* Header menu links only — 2px under `small`. There is no
                   matching btn-sm any more: `btn` came down to the same size, so
                   keeping both would have been two names for one value. */
                nav: ['clamp(0.90625rem, 0.883rem + 0.0952vw, 0.96875rem)', { lineHeight: '1.45' }],
            },

            /* Tailwind's built-in scale stops at 3xl (1.5rem). Cards and media panes
               use 4xl, the full-width dark panels and the hero use 5xl. */
            borderRadius: {
                '4xl': '2rem',
                '5xl': '2.5rem',
            },

            boxShadow: {
                soft: '0 18px 40px -18px rgba(35,10,14,.28)',
                lift: '0 30px 70px -28px rgba(35,10,14,.45)',
                glow: '0 10px 22px -12px rgba(200,16,46,.9)',
                /* Premium: layered shadows. A real object casts a tight contact
                   shadow and a wide ambient one; a single blur never reads as
                   solid. These stack both, which is most of the "expensive"
                   feel on the cards. */
                card: '0 1px 2px rgba(35,10,14,.05), 0 8px 20px -10px rgba(35,10,14,.16), 0 30px 60px -35px rgba(35,10,14,.30)',
                'card-hover': '0 2px 4px rgba(35,10,14,.06), 0 16px 32px -12px rgba(35,10,14,.22), 0 48px 90px -40px rgba(35,10,14,.42)',
                inset: 'inset 0 1px 0 0 rgba(255,255,255,.08)',
            },

            /* Premium: one easing vocabulary. `smooth` for most state changes,
               `entrance` for things arriving on screen (slight overshoot-free
               deceleration), `exit` for things leaving. */
            transitionTimingFunction: {
                smooth: 'cubic-bezier(.22,.61,.36,1)',
                entrance: 'cubic-bezier(.16,.84,.44,1)',
                exit: 'cubic-bezier(.55,.06,.68,.19)',
            },

            transitionDuration: {
                400: '400ms',
                600: '600ms',
            },

            keyframes: {
                'marquee-scroll': {
                    from: { transform: 'translateX(0)' },
                    to: { transform: 'translateX(-100%)' },
                },
                'sheen-sweep': {
                    '0%': { transform: 'translateX(-120%) skewX(-18deg)' },
                    '100%': { transform: 'translateX(320%) skewX(-18deg)' },
                },
            },
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
    ],
};
