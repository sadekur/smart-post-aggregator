import React from 'react';
import { IconArrowLeft, IconArrowRight, Spinner } from './Icons';

export const PageShell = ({ children, maxWidth = 'max-w-5xl' }) => (
	<div className={`${maxWidth} mx-auto px-4 sm:px-6 py-8`}>{children}</div>
);

export const PageHeader = ({ icon: Icon, title, subtitle, action }) => (
	<div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
		<div className="flex items-center gap-3">
			<div className="inline-flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm shadow-indigo-200">
				<Icon className="w-6 h-6" />
			</div>
			<div>
				<h1 className="text-xl font-bold text-gray-900 leading-tight">{title}</h1>
				{subtitle && <p className="text-sm text-gray-500">{subtitle}</p>}
			</div>
		</div>
		{action}
	</div>
);

export const Card = ({ children, className = '', title, icon: Icon, actions }) => (
	<div className={`bg-white rounded-xl shadow-sm ring-1 ring-gray-200 ${className}`}>
		{(title || actions) && (
			<div className="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-100">
				<h3 className="flex items-center gap-2 font-semibold text-gray-900">
					{Icon && <Icon className="w-4 h-4 text-gray-400" />}
					{title}
				</h3>
				{actions}
			</div>
		)}
		<div className="p-6">{children}</div>
	</div>
);

const BADGE_COLORS = {
	green: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
	red: 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200',
	amber: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
	blue: 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200',
	indigo: 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200',
	gray: 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-200',
};

export const Badge = ({ color = 'gray', icon: Icon, children, className = '' }) => (
	<span
		className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap ${BADGE_COLORS[color]} ${className}`}
	>
		{Icon && <Icon className="w-3.5 h-3.5" />}
		{children}
	</span>
);

const BUTTON_VARIANTS = {
	primary: 'bg-indigo-600 text-white hover:bg-indigo-700 focus-visible:ring-indigo-500 shadow-sm shadow-indigo-200',
	secondary: 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus-visible:ring-indigo-500',
	success: 'bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:ring-emerald-500 shadow-sm shadow-emerald-200',
	warning: 'bg-amber-500 text-white hover:bg-amber-600 focus-visible:ring-amber-400 shadow-sm shadow-amber-200',
	danger: 'bg-rose-600 text-white hover:bg-rose-700 focus-visible:ring-rose-500 shadow-sm shadow-rose-200',
	ghost: 'text-gray-500 hover:text-rose-600 hover:bg-rose-50 focus-visible:ring-rose-500',
};

const BUTTON_SIZES = {
	sm: 'px-3 py-1.5 text-xs gap-1.5',
	md: 'px-4 py-2.5 text-sm gap-2',
};

export const Button = ({
	variant = 'primary',
	size = 'md',
	icon: Icon,
	loading = false,
	disabled = false,
	className = '',
	children,
	...props
}) => (
	<button
		className={`inline-flex items-center justify-center font-semibold rounded-lg transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed ${BUTTON_SIZES[size]} ${BUTTON_VARIANTS[variant]} ${className}`}
		disabled={disabled || loading}
		{...props}
	>
		{loading ? <Spinner className="w-4 h-4" /> : Icon && <Icon className="w-4 h-4" />}
		{children}
	</button>
);

const STAT_COLORS = {
	indigo: 'bg-indigo-50 text-indigo-600',
	sky: 'bg-sky-50 text-sky-600',
	violet: 'bg-violet-50 text-violet-600',
	emerald: 'bg-emerald-50 text-emerald-600',
	amber: 'bg-amber-50 text-amber-600',
	rose: 'bg-rose-50 text-rose-600',
};

export const StatTile = ({ icon: Icon, label, value, color = 'indigo', highlight = false }) => (
	<div
		className={`rounded-xl bg-white p-5 shadow-sm ring-1 transition-shadow hover:shadow-md ${
			highlight ? 'ring-rose-200' : 'ring-gray-200'
		}`}
	>
		<div className={`inline-flex items-center justify-center w-10 h-10 rounded-lg mb-3 ${STAT_COLORS[color]}`}>
			<Icon className="w-5 h-5" />
		</div>
		<div className={`text-2xl font-bold ${highlight ? 'text-rose-600' : 'text-gray-900'}`}>{value}</div>
		<div className="text-sm text-gray-500 mt-0.5">{label}</div>
	</div>
);

export const EmptyState = ({ icon: Icon, title, description }) => (
	<div className="flex flex-col items-center justify-center text-center py-12">
		<div className="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gray-50 text-gray-300 mb-4">
			<Icon className="w-7 h-7" />
		</div>
		<p className="font-medium text-gray-700">{title}</p>
		{description && <p className="text-sm text-gray-400 mt-1">{description}</p>}
	</div>
);

export const LoadingState = ({ label = 'Loading…' }) => (
	<div className="flex items-center justify-center gap-2 text-gray-400 py-12">
		<Spinner />
		<span className="text-sm">{label}</span>
	</div>
);

export const Pagination = ({ currentPage, totalPages, onPrevious, onNext }) => (
	<div className="flex items-center justify-between">
		<Button variant="secondary" size="sm" icon={IconArrowLeft} onClick={onPrevious} disabled={currentPage <= 1}>
			Previous
		</Button>
		<span className="text-xs font-medium text-gray-400">
			Page {currentPage} of {totalPages}
		</span>
		<Button variant="primary" size="sm" onClick={onNext} disabled={currentPage >= totalPages}>
			Next
			<IconArrowRight className="w-4 h-4" />
		</Button>
	</div>
);

export const Alert = ({ type = 'success', children }) => {
	const styles =
		type === 'success'
			? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200'
			: 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200';

	return <div className={`rounded-lg px-4 py-3 text-sm font-medium mb-4 ${styles}`}>{children}</div>;
};

const SOURCE_STATUS = {
	active: { color: 'green', label: 'Active' },
	error: { color: 'red', label: 'Error' },
	paused: { color: 'amber', label: 'Paused' },
};

export const sourceStatus = (status) => SOURCE_STATUS[status] || { color: 'gray', label: status };

const RESOLUTION = {
	queued: { color: 'amber', label: 'Pending review' },
	marked: { color: 'gray', label: 'Marked' },
	merged: { color: 'blue', label: 'Merged' },
	ignored: { color: 'red', label: 'Ignored' },
	approved: { color: 'green', label: 'Approved unique' },
};

export const resolutionStatus = (resolution) => RESOLUTION[resolution] || { color: 'gray', label: resolution };

export const scoreColor = (score) => {
	if (score >= 80) {
		return 'red';
	}
	if (score >= 50) {
		return 'amber';
	}
	return 'gray';
};
