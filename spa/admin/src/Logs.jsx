import React, { useState, useEffect } from 'react';

const Logs = () => {
	const [logs, setLogs] = useState([]);
	const [loading, setLoading] = useState(true);
	const [currentPage, setCurrentPage] = useState(1);
	const [totalPages, setTotalPages] = useState(1);
	const [filters, setFilters] = useState({ resolution: '', date_from: '', date_to: '' });

	const per_page = 20;

	useEffect(() => {
		const fetch_logs = async () => {
			setLoading(true);
			try {
				const params = new URLSearchParams({
					page: currentPage,
					per_page,
					...(filters.resolution && { resolution: filters.resolution }),
					...(filters.date_from && { date_from: filters.date_from }),
					...(filters.date_to && { date_to: filters.date_to }),
				});

				const response = await fetch(`${SPA_PLUGIN_ADMIN.api_base}/logs?${params.toString()}`, {
					headers: { 'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce },
				});
				const data = await response.json();

				setTotalPages(parseInt(response.headers.get('X-WP-TotalPages'), 10) || 1);
				setLogs(Array.isArray(data) ? data : []);
			} catch (err) {
				console.error('Error fetching logs:', err);
				setLogs([]);
			} finally {
				setLoading(false);
			}
		};

		fetch_logs();
	}, [currentPage, filters]);

	const handle_filter_change = (event) => {
		const { name, value } = event.target;
		setCurrentPage(1);
		setFilters((prev) => ({ ...prev, [name]: value }));
	};

	return (
		<div className="min-h-screen bg-gray-100 flex flex-col items-center py-8">
			<div className="w-full max-w-4xl bg-white shadow-md rounded-lg p-6 mb-4">
				<h3 className="text-lg font-semibold mb-4">Duplicate Detection Logs</h3>

				<div className="flex gap-3 mb-4">
					<select
						name="resolution"
						value={filters.resolution}
						onChange={handle_filter_change}
						className="border rounded py-2 px-3 text-gray-700"
					>
						<option value="">All resolutions</option>
						<option value="queued">Pending review</option>
						<option value="marked">Marked</option>
						<option value="merged">Merged</option>
						<option value="ignored">Ignored</option>
						<option value="approved">Approved as unique</option>
					</select>

					<input
						type="date"
						name="date_from"
						value={filters.date_from}
						onChange={handle_filter_change}
						className="border rounded py-2 px-3 text-gray-700"
					/>

					<input
						type="date"
						name="date_to"
						value={filters.date_to}
						onChange={handle_filter_change}
						className="border rounded py-2 px-3 text-gray-700"
					/>
				</div>

				{loading && <p className="text-gray-500">Loading...</p>}

				{!loading && logs.length === 0 && (
					<p className="text-gray-500 text-center py-4">No log entries match these filters.</p>
				)}

				{!loading && logs.length > 0 && (
					<table className="w-full text-left text-sm">
						<thead>
							<tr className="border-b">
								<th className="py-2">Date</th>
								<th className="py-2">New Post</th>
								<th className="py-2">Matched Post</th>
								<th className="py-2">Score</th>
								<th className="py-2">Algorithm</th>
								<th className="py-2">Resolution</th>
								<th className="py-2">Resolved By</th>
							</tr>
						</thead>
						<tbody>
							{logs.map((log) => (
								<tr key={log.log_id} className="border-b">
									<td className="py-2 whitespace-nowrap">{log.created_at}</td>
									<td className="py-2">
										<a
											href={log.new_post.link}
											target="_blank"
											rel="noreferrer"
											className="text-blue-600 hover:underline"
											dangerouslySetInnerHTML={{ __html: log.new_post.title }}
										></a>
									</td>
									<td className="py-2">
										{log.matched_post ? (
											<a
												href={log.matched_post.link}
												target="_blank"
												rel="noreferrer"
												className="text-blue-600 hover:underline"
												dangerouslySetInnerHTML={{ __html: log.matched_post.title }}
											></a>
										) : (
											<span className="text-gray-400 italic">—</span>
										)}
									</td>
									<td className="py-2">{log.score}%</td>
									<td className="py-2">{log.algorithm}</td>
									<td className="py-2">{log.resolution}</td>
									<td className="py-2">{log.resolved_by || <span className="text-gray-400 italic">—</span>}</td>
								</tr>
							))}
						</tbody>
					</table>
				)}
			</div>

			<div className="flex justify-between w-full max-w-4xl">
				<button
					className="px-4 py-2 bg-gray-300 text-gray-700 rounded disabled:opacity-50"
					onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
					disabled={currentPage === 1}
				>
					Previous
				</button>
				<button
					className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700 disabled:opacity-50"
					onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
					disabled={currentPage >= totalPages}
				>
					Next
				</button>
			</div>
		</div>
	);
};

export default Logs;
