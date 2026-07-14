import React, { useState, useEffect } from 'react';
import { IconDocumentText, IconFilter, IconCalendar } from './components/Icons';
import { PageShell, PageHeader, Card, Badge, EmptyState, TableRowSkeleton, Pagination, resolutionStatus, scoreColor } from './components/UI';

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
		<PageShell maxWidth="max-w-5xl">
			<PageHeader icon={IconDocumentText} title="Logs" subtitle="Full audit trail of every duplicate-detection event" />

			<Card title="Filters" icon={IconFilter} className="mb-6">
				<div className="flex flex-wrap gap-3">
					<select
						name="resolution"
						value={filters.resolution}
						onChange={handle_filter_change}
						className="rounded-lg border border-gray-300 py-2 px-3 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none"
					>
						<option value="">All resolutions</option>
						<option value="queued">Pending review</option>
						<option value="marked">Marked</option>
						<option value="merged">Merged</option>
						<option value="ignored">Ignored</option>
						<option value="approved">Approved as unique</option>
					</select>

					<label className="flex items-center gap-2 rounded-lg border border-gray-300 py-2 px-3 text-sm text-gray-500 shadow-sm">
						<IconCalendar className="w-4 h-4 text-gray-400" />
						<input
							type="date"
							name="date_from"
							value={filters.date_from}
							onChange={handle_filter_change}
							className="text-gray-700 focus:outline-none"
						/>
					</label>

					<label className="flex items-center gap-2 rounded-lg border border-gray-300 py-2 px-3 text-sm text-gray-500 shadow-sm">
						<IconCalendar className="w-4 h-4 text-gray-400" />
						<input
							type="date"
							name="date_to"
							value={filters.date_to}
							onChange={handle_filter_change}
							className="text-gray-700 focus:outline-none"
						/>
					</label>
				</div>
			</Card>

			<Card className="mb-4">
				{!loading && logs.length === 0 && (
					<EmptyState icon={IconDocumentText} title="No log entries" description="No events match these filters." />
				)}

				{(loading || logs.length > 0) && (
					<div className="overflow-x-auto -mx-6">
						<table className="w-full text-left text-sm">
							<thead>
								<tr className="text-xs uppercase tracking-wide text-gray-400 border-b border-gray-100">
									<th className="py-2.5 px-6 font-semibold">Date</th>
									<th className="py-2.5 px-3 font-semibold">New Post</th>
									<th className="py-2.5 px-3 font-semibold">Matched Post</th>
									<th className="py-2.5 px-3 font-semibold">Score</th>
									<th className="py-2.5 px-3 font-semibold">Algorithm</th>
									<th className="py-2.5 px-3 font-semibold">Resolution</th>
									<th className="py-2.5 px-6 font-semibold">Resolved By</th>
								</tr>
							</thead>
							<tbody>
								{loading &&
									Array.from({ length: 8 }).map((_, i) => <TableRowSkeleton key={i} columns={7} />)}

								{!loading &&
									logs.map((log) => {
										const resolution = resolutionStatus(log.resolution);
										return (
											<tr key={log.log_id} className="border-b border-gray-50 hover:bg-gray-50/70 transition-colors">
												<td className="py-3 px-6 whitespace-nowrap text-gray-500">{log.created_at}</td>
												<td className="py-3 px-3 max-w-[220px]">
													<a
														href={log.new_post.link}
														target="_blank"
														rel="noreferrer"
														className="text-gray-800 hover:text-indigo-600 font-medium line-clamp-1"
														dangerouslySetInnerHTML={{ __html: log.new_post.title }}
													></a>
												</td>
												<td className="py-3 px-3 max-w-[220px]">
													{log.matched_post ? (
														<a
															href={log.matched_post.link}
															target="_blank"
															rel="noreferrer"
															className="text-gray-800 hover:text-indigo-600 font-medium line-clamp-1"
															dangerouslySetInnerHTML={{ __html: log.matched_post.title }}
														></a>
													) : (
														<span className="text-gray-300 italic">—</span>
													)}
												</td>
												<td className="py-3 px-3">
													<Badge color={scoreColor(log.score)}>{log.score}%</Badge>
												</td>
												<td className="py-3 px-3 text-gray-500">{log.algorithm}</td>
												<td className="py-3 px-3">
													<Badge color={resolution.color}>{resolution.label}</Badge>
												</td>
												<td className="py-3 px-6 text-gray-500">{log.resolved_by || <span className="text-gray-300 italic">—</span>}</td>
											</tr>
										);
									})}
							</tbody>
						</table>
					</div>
				)}
			</Card>

			<Pagination
				currentPage={currentPage}
				totalPages={totalPages}
				onPrevious={() => setCurrentPage((p) => Math.max(1, p - 1))}
				onNext={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
			/>
		</PageShell>
	);
};

export default Logs;
