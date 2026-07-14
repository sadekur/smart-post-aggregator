import React, { useState, useEffect } from 'react';
import { IconInbox, IconCheckCircle, IconAlertTriangle, IconMerge, IconXCircle, IconImage } from './components/Icons';
import { PageShell, PageHeader, Card, Badge, Button, EmptyState, ReviewCardSkeleton, scoreColor } from './components/UI';

const ACTIONS = [
	{ key: 'approve_unique', label: 'Approve Unique', variant: 'success', icon: IconCheckCircle },
	{ key: 'confirm_duplicate', label: 'Confirm Duplicate', variant: 'warning', icon: IconAlertTriangle },
	{ key: 'merge', label: 'Merge', variant: 'primary', icon: IconMerge },
	{ key: 'ignore', label: 'Ignore', variant: 'danger', icon: IconXCircle },
];

const Review = () => {
	const [items, setItems] = useState([]);
	const [loading, setLoading] = useState(true);

	const fetch_items = async () => {
		setLoading(true);
		try {
			const response = await fetch(`${SPA_PLUGIN_ADMIN.api_base}/duplicates`, {
				headers: { 'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce },
			});
			const data = await response.json();
			setItems(Array.isArray(data) ? data : []);
		} catch (err) {
			console.error('Error fetching pending duplicates:', err);
			setItems([]);
		} finally {
			setLoading(false);
		}
	};

	useEffect(() => {
		fetch_items();
	}, []);

	const handle_action = async (log_id, action) => {
		try {
			await fetch(`${SPA_PLUGIN_ADMIN.api_base}/duplicates/${log_id}/resolve`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce,
				},
				body: JSON.stringify({ action }),
			});
			setItems((prev) => prev.filter((item) => item.log_id !== log_id));
		} catch (err) {
			console.error('Error resolving duplicate:', err);
		}
	};

	const render_preview = (post) => {
		if (!post) {
			return <p className="text-sm text-gray-400 italic">No matched post</p>;
		}

		return (
			<div className="flex items-center gap-3">
				{post.thumbnail ? (
					<img src={post.thumbnail} alt="" className="w-11 h-11 object-cover rounded-lg ring-1 ring-gray-200 shrink-0" />
				) : (
					<span className="inline-flex items-center justify-center w-11 h-11 rounded-lg bg-gray-50 text-gray-300 shrink-0">
						<IconImage className="w-5 h-5" />
					</span>
				)}
				<a
					href={post.link}
					target="_blank"
					rel="noreferrer"
					className="text-sm text-gray-800 hover:text-indigo-600 font-medium leading-snug"
					dangerouslySetInnerHTML={{ __html: post.title }}
				></a>
			</div>
		);
	};

	return (
		<PageShell maxWidth="max-w-4xl">
			<PageHeader icon={IconInbox} title="Review" subtitle="Possible duplicates waiting on a manual decision" />

			{loading && (
				<Card>
					<LoadingState label="Loading review queue…" />
				</Card>
			)}

			{!loading && items.length === 0 && (
				<Card>
					<EmptyState icon={IconCheckCircle} title="All caught up" description="Nothing is waiting on manual review right now." />
				</Card>
			)}

			{!loading && items.length > 0 && (
				<div className="space-y-4">
					{items.map((item) => (
						<Card key={item.log_id} className="overflow-visible">
							<div className="flex items-center justify-between gap-3 mb-4">
								<Badge color={scoreColor(item.score)}>{item.score}% similar</Badge>
								<Badge color="gray">{item.algorithm}</Badge>
							</div>

							<div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
								<div className="rounded-lg bg-gray-50/70 p-3">
									<p className="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">New Item</p>
									{render_preview(item.new)}
								</div>
								<div className="rounded-lg bg-gray-50/70 p-3">
									<p className="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Matched Item</p>
									{render_preview(item.matched)}
								</div>
							</div>

							<div className="flex flex-wrap gap-2">
								{ACTIONS.map((action) => (
									<Button
										key={action.key}
										size="sm"
										variant={action.variant}
										icon={action.icon}
										onClick={() => handle_action(item.log_id, action.key)}
									>
										{action.label}
									</Button>
								))}
							</div>
						</Card>
					))}
				</div>
			)}
		</PageShell>
	);
};

export default Review;
