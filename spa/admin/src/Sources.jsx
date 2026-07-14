import React, { useState, useEffect } from 'react';
import { IconRss, IconGlobe, IconPlus, IconTrash, IconClock } from './components/Icons';
import { PageShell, PageHeader, Card, Badge, Button, EmptyState, TableRowSkeleton, Alert, sourceStatus } from './components/UI';

const TYPES = [
	{ value: 'rss', label: 'RSS Feed', icon: IconRss },
	{ value: 'api', label: 'JSON API', icon: IconGlobe },
];

const Sources = () => {
	const [sources, setSources] = useState([]);
	const [loading, setLoading] = useState(true);
	const [form, setForm] = useState({ name: '', type: 'rss', url: '', fetch_interval: 900 });
	const [error, setError] = useState('');
	const [submitting, setSubmitting] = useState(false);
	const [deletingId, setDeletingId] = useState(null);

	const fetch_sources = async () => {
		setLoading(true);
		try {
			const response = await fetch(`${SPA_PLUGIN_ADMIN.api_base}/sources`, {
				headers: { 'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce },
			});
			const data = await response.json();
			setSources(Array.isArray(data) ? data : []);
		} catch (err) {
			console.error('Error fetching sources:', err);
			setSources([]);
		} finally {
			setLoading(false);
		}
	};

	useEffect(() => {
		fetch_sources();
	}, []);

	const handle_change = (event) => {
		const { name, value } = event.target;
		setForm((prev) => ({ ...prev, [name]: value }));
	};

	const handle_type = (type) => {
		setForm((prev) => ({ ...prev, type }));
	};

	const handle_submit = async (event) => {
		event.preventDefault();
		setError('');
		setSubmitting(true);

		try {
			const response = await fetch(`${SPA_PLUGIN_ADMIN.api_base}/sources`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce,
				},
				body: JSON.stringify({
					...form,
					fetch_interval: parseInt(form.fetch_interval, 10) || 900,
				}),
			});

			const data = await response.json();

			if (!response.ok) {
				setError(data.message || 'Could not create source.');
				return;
			}

			setForm({ name: '', type: 'rss', url: '', fetch_interval: 900 });
			fetch_sources();
		} catch (err) {
			console.error('Error creating source:', err);
			setError('Could not create source.');
		} finally {
			setSubmitting(false);
		}
	};

	const handle_delete = async (id) => {
		setDeletingId(id);
		try {
			await fetch(`${SPA_PLUGIN_ADMIN.api_base}/sources/${id}`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce },
			});
			await fetch_sources();
		} catch (err) {
			console.error('Error deleting source:', err);
		} finally {
			setDeletingId(null);
		}
	};

	return (
		<PageShell maxWidth="max-w-5xl">
			<PageHeader icon={IconRss} title="Sources" subtitle="RSS feeds and JSON API endpoints the cron sweep aggregates from" />

			<div className="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-6 items-start">
				<Card title="Add Source" icon={IconPlus}>
					<form onSubmit={handle_submit} className="space-y-4">
						{error && <Alert type="error">{error}</Alert>}

						<div>
							<label className="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5" htmlFor="source-name">
								Name
							</label>
							<input
								id="source-name"
								name="name"
								type="text"
								value={form.name}
								onChange={handle_change}
								required
								placeholder="e.g. Hacker News Frontpage"
								className="w-full rounded-lg border border-gray-300 py-2.5 px-3 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none"
							/>
						</div>

						<div>
							<label className="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Type</label>
							<div className="grid grid-cols-2 gap-2">
								{TYPES.map((type) => {
									const Icon = type.icon;
									const active = form.type === type.value;
									return (
										<button
											type="button"
											key={type.value}
											onClick={() => handle_type(type.value)}
											className={`flex items-center justify-center gap-2 rounded-lg border py-2.5 text-sm font-medium transition-colors ${
												active
													? 'border-indigo-500 bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200'
													: 'border-gray-300 text-gray-500 hover:bg-gray-50'
											}`}
										>
											<Icon className="w-4 h-4" />
											{type.label}
										</button>
									);
								})}
							</div>
						</div>

						<div>
							<label className="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5" htmlFor="source-url">
								Feed URL
							</label>
							<input
								id="source-url"
								name="url"
								type="url"
								value={form.url}
								onChange={handle_change}
								required
								placeholder="https://example.com/feed"
								className="w-full rounded-lg border border-gray-300 py-2.5 px-3 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none"
							/>
						</div>

						<div>
							<label className="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5" htmlFor="source-interval">
								Fetch Interval (seconds)
							</label>
							<input
								id="source-interval"
								name="fetch_interval"
								type="number"
								min="60"
								value={form.fetch_interval}
								onChange={handle_change}
								className="w-full rounded-lg border border-gray-300 py-2.5 px-3 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none"
							/>
							<p className="text-xs text-gray-400 mt-1.5">How often this source is checked (900s = 15 minutes).</p>
						</div>

						<Button type="submit" icon={IconPlus} loading={submitting} className="w-full">
							{submitting ? 'Adding…' : 'Add Source'}
						</Button>
					</form>
				</Card>

				<Card title="All Sources" icon={IconRss}>
					{loading && <LoadingState label="Loading sources…" />}

					{!loading && sources.length === 0 && (
						<EmptyState icon={IconRss} title="No sources yet" description="Add a feed or API endpoint to start aggregating content." />
					)}

					{!loading && sources.length > 0 && (
						<div className="overflow-x-auto -mx-6">
							<table className="w-full text-left text-sm">
								<thead>
									<tr className="text-xs uppercase tracking-wide text-gray-400 border-b border-gray-100">
										<th className="py-2.5 px-6 font-semibold">Name</th>
										<th className="py-2.5 px-3 font-semibold">Type</th>
										<th className="py-2.5 px-3 font-semibold">Status</th>
										<th className="py-2.5 px-3 font-semibold">Last Fetched</th>
										<th className="py-2.5 px-6"></th>
									</tr>
								</thead>
								<tbody>
									{sources.map((source) => {
										const status = sourceStatus(source.status);
										const TypeIcon = source.type === 'api' ? IconGlobe : IconRss;
										return (
											<tr key={source.id} className="border-b border-gray-50 hover:bg-gray-50/70 transition-colors">
												<td className="py-3 px-6 font-medium text-gray-800">{source.name}</td>
												<td className="py-3 px-3">
													<Badge color="indigo" icon={TypeIcon}>
														{source.type}
													</Badge>
												</td>
												<td className="py-3 px-3">
													<Badge color={status.color}>{status.label}</Badge>
												</td>
												<td className="py-3 px-3 text-gray-500 whitespace-nowrap">
													<span className="inline-flex items-center gap-1.5">
														<IconClock className="w-3.5 h-3.5 text-gray-300" />
														{source.last_fetched_at || 'Never'}
													</span>
												</td>
												<td className="py-3 px-6 text-right">
													<Button variant="ghost" size="sm" icon={IconTrash} onClick={() => handle_delete(source.id)}>
														Delete
													</Button>
												</td>
											</tr>
										);
									})}
								</tbody>
							</table>
						</div>
					)}
				</Card>
			</div>
		</PageShell>
	);
};

export default Sources;
