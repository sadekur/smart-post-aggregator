import React, { useState, useEffect } from 'react';

const StatTile = ({ label, value, highlight }) => (
	<div className="bg-white shadow-md rounded-lg p-4 flex-1 min-w-[140px]">
		<div className={`text-2xl font-bold ${highlight ? 'text-red-600' : 'text-gray-800'}`}>{value}</div>
		<div className="text-sm text-gray-500">{label}</div>
	</div>
);

const Home = () => {
	const [posts, setPosts] = useState([]);
	const [totalPages, setTotalPages] = useState(1);
	const [currentPage, setCurrentPage] = useState(() => {
		const page_from_hash = window.location.hash.match(/#paged=(\d+)/);
		return page_from_hash ? parseInt(page_from_hash[1], 10) : 1;
	});
	const [stats, setStats] = useState(null);

	const posts_per_page = 8;

	useEffect(() => {
		const fetch_stats = async () => {
			try {
				const response = await fetch(`${SPA_PLUGIN_ADMIN.api_base}/stats`, {
					headers: { 'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce },
				});
				const data = await response.json();
				setStats(data);
			} catch (error) {
				console.error('Error fetching stats:', error);
			}
		};

		fetch_stats();
	}, []);

	useEffect(() => {
		const fetch_data = async () => {
			spa_modal(true);
			try {
				const url = `${SPA_PLUGIN_ADMIN.api_base}/content?page=${currentPage}&per_page=${posts_per_page}`;
				const response = await fetch(url, {
					headers: { 'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce },
				});

				const data = await response.json();

				setTotalPages(parseInt(response.headers.get('X-WP-TotalPages'), 10) || 1);

				setPosts(Array.isArray(data) ? data : []);
			} catch (error) {
				console.error('Error fetching aggregated content:', error);
				setPosts([]);
			} finally {
				spa_modal(false);
			}
		};

		fetch_data();
	}, [currentPage]);

	useEffect(() => {
		window.location.hash = `#paged=${currentPage}`;
	}, [currentPage]);

	const handle_previous = () => {
		if (currentPage > 1) {
			setCurrentPage(currentPage - 1);
		}
	};

	const handle_next = () => {
		if (currentPage < totalPages) {
			setCurrentPage(currentPage + 1);
		}
	};

	return (
		<div className="min-h-screen bg-gray-100 flex flex-col items-center py-8">
			{stats && (
				<div className="w-full max-w-2xl flex flex-wrap gap-3 mb-4">
					<StatTile label="Aggregated Today" value={stats.aggregated_today} />
					<StatTile label="Aggregated This Week" value={stats.aggregated_week} />
					<StatTile label="Total Aggregated" value={stats.aggregated_total} />
					<StatTile label="Auto-Resolved" value={stats.auto_resolved} />
					<StatTile label="Pending Review" value={stats.pending_review} highlight={stats.pending_review > 0} />
					<StatTile label="Sources In Error" value={stats.sources_in_error} highlight={stats.sources_in_error > 0} />
				</div>
			)}

			{stats && stats.recent_activity.length > 0 && (
				<div className="w-full max-w-2xl bg-white shadow-md rounded-lg p-6 mb-4">
					<h3 className="text-lg font-semibold mb-3">Recent Activity</h3>
					<ul>
						{stats.recent_activity.map((activity) => (
							<li key={activity.log_id} className="text-sm text-gray-600 border-b py-2 last:border-0">
								<span
									className="font-medium"
									dangerouslySetInnerHTML={{ __html: activity.new_title }}
								></span>
								{activity.matched_title && (
									<>
										{' '}
										matched{' '}
										<span dangerouslySetInnerHTML={{ __html: activity.matched_title }}></span>
									</>
								)}{' '}
								&mdash; {activity.score}% ({activity.resolution})
							</li>
						))}
					</ul>
				</div>
			)}

			<ol className="w-full max-w-2xl bg-white shadow-md rounded-lg p-6 mb-4">
				{posts.length === 0 && (
					<li className="text-gray-500 text-center py-4">No aggregated content yet.</li>
				)}
				{posts.map((post) => (
					<li key={post.id} className="mb-4 border-b pb-4 flex items-center">
						{post.thumbnail && (
							<img
								src={post.thumbnail}
								alt={post.title}
								className="w-8 h-8 object-cover mr-4 rounded"
							/>
						)}
						<a
							href={post.link}
							target="_blank"
							rel="noreferrer"
							className="text-blue-600 hover:underline"
							dangerouslySetInnerHTML={{ __html: post.title }}
						></a>
					</li>
				))}
			</ol>

			<div className="flex justify-between w-full max-w-2xl">
				<button
					className="px-4 py-2 bg-gray-300 text-gray-700 rounded disabled:opacity-50"
					onClick={handle_previous}
					disabled={currentPage === 1}
				>
					Previous
				</button>
				<button
					className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700 disabled:opacity-50"
					onClick={handle_next}
					disabled={currentPage >= totalPages}
				>
					Next
				</button>
			</div>
		</div>
	);
}

export default Home;
