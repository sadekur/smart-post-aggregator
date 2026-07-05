import React, { useState, useEffect } from 'react';

const ACTIONS = [
	{ key: 'approve_unique', label: 'Approve as Unique', className: 'bg-green-500 hover:bg-green-700' },
	{ key: 'confirm_duplicate', label: 'Confirm Duplicate', className: 'bg-yellow-500 hover:bg-yellow-700' },
	{ key: 'merge', label: 'Merge', className: 'bg-blue-500 hover:bg-blue-700' },
	{ key: 'ignore', label: 'Ignore', className: 'bg-red-500 hover:bg-red-700' },
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
			return <p className="text-gray-400 italic">No matched post</p>;
		}

		return (
			<div className="flex items-center gap-3">
				{post.thumbnail && (
					<img src={post.thumbnail} alt={post.title} className="w-10 h-10 object-cover rounded" />
				)}
				<a
					href={post.link}
					target="_blank"
					rel="noreferrer"
					className="text-blue-600 hover:underline font-medium"
					dangerouslySetInnerHTML={{ __html: post.title }}
				></a>
			</div>
		);
	};

	return (
		<div className="min-h-screen bg-gray-100 flex flex-col items-center py-8">
			<div className="w-full max-w-3xl bg-white shadow-md rounded-lg p-6">
				<h3 className="text-lg font-semibold mb-4">Pending Review</h3>

				{loading && <p className="text-gray-500">Loading...</p>}

				{!loading && items.length === 0 && (
					<p className="text-gray-500 text-center py-4">Nothing pending review.</p>
				)}

				{!loading &&
					items.map((item) => (
						<div key={item.log_id} className="border-b py-4">
							<div className="flex justify-between items-center mb-2">
								<span className="text-sm text-gray-500">
									{item.algorithm} &middot; {item.score}% similar
								</span>
							</div>

							<div className="grid grid-cols-2 gap-4 mb-3">
								<div>
									<p className="text-xs uppercase text-gray-400 mb-1">New</p>
									{render_preview(item.new)}
								</div>
								<div>
									<p className="text-xs uppercase text-gray-400 mb-1">Matched</p>
									{render_preview(item.matched)}
								</div>
							</div>

							<div className="flex gap-2">
								{ACTIONS.map((action) => (
									<button
										key={action.key}
										onClick={() => handle_action(item.log_id, action.key)}
										className={`px-3 py-1 text-sm text-white rounded ${action.className}`}
									>
										{action.label}
									</button>
								))}
							</div>
						</div>
					))}
			</div>
		</div>
	);
};

export default Review;
