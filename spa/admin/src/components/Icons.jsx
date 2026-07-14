import React from 'react';

const base = {
	viewBox: '0 0 24 24',
	fill: 'none',
	stroke: 'currentColor',
	strokeWidth: 1.75,
	strokeLinecap: 'round',
	strokeLinejoin: 'round',
};

export const IconChartBar = ({ className = 'w-5 h-5' }) => (
	<svg {...base} stroke="none" fill="currentColor" className={className}>
		<rect x="4" y="12" width="3.5" height="8" rx="1" />
		<rect x="10.25" y="7" width="3.5" height="13" rx="1" />
		<rect x="16.5" y="3" width="3.5" height="17" rx="1" />
	</svg>
);

export const IconLayers = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<polygon points="12 3 20 7.5 12 12 4 7.5" />
		<polyline points="4 12 12 16.5 20 12" />
		<polyline points="4 16.5 12 21 20 16.5" />
	</svg>
);

export const IconRss = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<circle cx="6" cy="18" r="1.75" fill="currentColor" stroke="none" />
		<path d="M4 11a9 9 0 0 1 9 9" />
		<path d="M4 4a16 16 0 0 1 16 16" />
	</svg>
);

export const IconGlobe = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<circle cx="12" cy="12" r="9" />
		<ellipse cx="12" cy="12" rx="4" ry="9" />
		<line x1="3" y1="12" x2="21" y2="12" />
	</svg>
);

export const IconClock = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<circle cx="12" cy="12" r="9" />
		<polyline points="12 7 12 12 15.5 14" />
	</svg>
);

export const IconCheckCircle = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<circle cx="12" cy="12" r="9" />
		<polyline points="8 12.5 11 15.5 16 9" />
	</svg>
);

export const IconAlertTriangle = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<polygon points="12 3 22 20 2 20" />
		<line x1="12" y1="9" x2="12" y2="13.5" />
		<circle cx="12" cy="16.5" r="0.9" fill="currentColor" stroke="none" />
	</svg>
);

export const IconTrash = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<rect x="6" y="7" width="12" height="13" rx="1.5" />
		<line x1="4" y1="7" x2="20" y2="7" />
		<line x1="9" y1="7" x2="9" y2="4.5" />
		<line x1="9" y1="4.5" x2="15" y2="4.5" />
		<line x1="15" y1="4.5" x2="15" y2="7" />
		<line x1="10" y1="11" x2="10" y2="17" />
		<line x1="14" y1="11" x2="14" y2="17" />
	</svg>
);

export const IconPlus = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<line x1="12" y1="5" x2="12" y2="19" />
		<line x1="5" y1="12" x2="19" y2="12" />
	</svg>
);

export const IconFilter = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<polygon points="4 4 20 4 14 12 14 19 10 21 10 12" />
	</svg>
);

export const IconCalendar = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<rect x="3" y="5" width="18" height="16" rx="2" />
		<line x1="16" y1="3" x2="16" y2="7" />
		<line x1="8" y1="3" x2="8" y2="7" />
		<line x1="3" y1="10" x2="21" y2="10" />
	</svg>
);

export const IconArrowLeft = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<line x1="19" y1="12" x2="5" y2="12" />
		<polyline points="12 19 5 12 12 5" />
	</svg>
);

export const IconArrowRight = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<line x1="5" y1="12" x2="19" y2="12" />
		<polyline points="12 5 19 12 12 19" />
	</svg>
);

export const IconSliders = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<line x1="4" y1="6" x2="20" y2="6" />
		<circle cx="9" cy="6" r="2" />
		<line x1="4" y1="12" x2="20" y2="12" />
		<circle cx="15" cy="12" r="2" />
		<line x1="4" y1="18" x2="20" y2="18" />
		<circle cx="7" cy="18" r="2" />
	</svg>
);

export const IconDocumentText = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<rect x="5" y="3" width="14" height="18" rx="2" />
		<line x1="8" y1="8" x2="16" y2="8" />
		<line x1="8" y1="12" x2="16" y2="12" />
		<line x1="8" y1="16" x2="13" y2="16" />
	</svg>
);

export const IconExternalLink = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<polyline points="14 4 20 4 20 10" />
		<line x1="20" y1="4" x2="11" y2="13" />
		<polyline points="18 13 18 19 5 19 5 6 11 6" />
	</svg>
);

export const IconMerge = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<line x1="6" y1="4" x2="12" y2="12" />
		<line x1="18" y1="4" x2="12" y2="12" />
		<line x1="12" y1="12" x2="12" y2="19" />
		<polyline points="9 16 12 19 15 16" />
	</svg>
);

export const IconXCircle = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<circle cx="12" cy="12" r="9" />
		<line x1="9" y1="9" x2="15" y2="15" />
		<line x1="15" y1="9" x2="9" y2="15" />
	</svg>
);

export const IconInbox = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<rect x="3" y="10" width="18" height="10" rx="2" />
		<polyline points="3 10 8 10 9.5 13 14.5 13 16 10 21 10" />
	</svg>
);

export const IconImage = ({ className = 'w-5 h-5' }) => (
	<svg {...base} className={className}>
		<rect x="3" y="4" width="18" height="16" rx="2" />
		<circle cx="9" cy="10" r="1.75" />
		<polyline points="4 18 9.5 12.5 13 16 16.5 12.5 20 16" />
	</svg>
);

export const Spinner = ({ className = 'w-5 h-5' }) => (
	<svg className={`animate-spin ${className}`} viewBox="0 0 24 24" fill="none">
		<circle className="opacity-20" cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="3" />
		<path className="opacity-90" d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
	</svg>
);
