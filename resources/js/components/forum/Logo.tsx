export default function Logo({ className = "h-8 w-8" }: { className?: string }) {
  return (
    <svg 
      xmlns="http://www.w3.org/2000/svg" 
      viewBox="0 0 120 120" 
      className={className}
    >
      {/* Fondo circular */}
      <defs>
        <linearGradient id="grad" x1="0" x2="1" y1="0" y2="1">
          <stop offset="0" stopColor="#2CCFA3"/>
          <stop offset="1" stopColor="#13A66B"/>
        </linearGradient>

        <filter id="shadow" x="-40%" y="-40%" width="180%" height="180%">
          <feDropShadow dx="0" dy="4" stdDeviation="6" floodColor="#0b6b4a" floodOpacity="0.18"/>
        </filter>
      </defs>

      {/* Círculo principal */}
      <circle cx="60" cy="60" r="50" fill="url(#grad)" filter="url(#shadow)"/>

      {/* Nube moderna y simétrica */}
      <path
        d="M79 66c4 0 7-3.3 7-7.5s-3-7.5-7-7.5c-.8 0-1.6.1-2.3.3C74 46 66.5 43 60 46.5c-3.5-4-10-5-14.5-1.8-4 3-5.2 8.4-3 12.7-3.8.9-6.5 4.3-6.5 8.2C36 70.3 39.7 74 44.3 74H79c6.2 0 11.2-5 11.2-11.2S85.2 66 79 66z"
        fill="white"
        opacity="0.97"
        stroke="rgba(255,255,255,0.15)"
        strokeWidth="1.5"
        strokeLinejoin="round"
      />
    </svg>
  );
}
