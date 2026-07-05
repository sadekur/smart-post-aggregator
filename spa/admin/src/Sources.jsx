import React, { useState, useEffect } from 'react';

const Sources = () => {
	const [sources, setSources] = useState([]);
	const [loading, setLoading] = useState(true);
	const [form, setForm] = useState({ name: '', type: 'rss', url: '', fetch_interval: 900 });
	const [error, setError] = useState('');

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

	const handle_submit = async (event) => {
		event.preventDefault();
		setError('');

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
		}
	};

	const handle_delete = async (id) => {
		try {
			await fetch(`${SPA_PLUGIN_ADMIN.api_base}/sources/${id}`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce },
			});
			fetch_sources();
		} catch (err) {
			console.error('Error deleting source:', err);
		}
	};

	return (
		<div className="min-h-screen bg-gray-100 flex flex-col items-center py-8">
			<form onSubmit={handle_submit} className="w-full max-w-2xl bg-white shadow-md rounded-lg p-6 mb-6">
				<h3 className="text-lg font-semibold mb-4">Add Source</h3>

				{error && <p className="text-red-600 mb-3">{error}</p>}

				<div className="mb-3">
					<label className="block text-gray-700 text-sm font-bold mb-1" htmlFor="source-name">
						Name
					</label>
					<input
						id="source-name"
						name="name"
						type="text"
						value={form.name}
						onChange={handle_change}
						required
						className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
					/>
				</div>

				<div className="mb-3">
					<label className="block text-gray-700 text-sm font-bold mb-1" htmlFor="source-type">
						Type
					</label>
					<select
						id="source-type"
						name="type"
						value={form.type}
						onChange={handle_change}
						className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
					>
						<option value="rss">RSS</option>
						<option value="api">API</option>
					</select>
				</div>

				<div className="mb-3">
					<label className="block text-gray-700 text-sm font-bold mb-1" htmlFor="source-url">
						Feed URL
					</label>
					<input
						id="source-url"
						name="url"
						type="url"
						value={form.url}
						onChange={handle_change}
						required
						className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
					/>
				</div>

				<div className="mb-4">
					<label className="block text-gray-700 text-sm font-bold mb-1" htmlFor="source-interval">
						Fetch Interval (seconds)
					</label>
					<input
						id="source-interval"
						name="fetch_interval"
						type="number"
						min="60"
						value={form.fetch_interval}
						onChange={handle_change}
						className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
					/>
				</div>

				<button
					type="submit"
					className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
				>
					Add Source
				</button>
			</form>

			<div className="w-full max-w-2xl bg-white shadow-md rounded-lg p-6">
				<h3 className="text-lg font-semibold mb-4">Sources</h3>

				{loading && <p className="text-gray-500">Loading...</p>}

				{!loading && sources.length === 0 && (
					<p className="text-gray-500 text-center py-4">No sources yet.</p>
				)}

				{!loading && sources.length > 0 && (
					<table className="w-full text-left">
						<thead>
							<tr className="border-b">
								<th className="py-2">Name</th>
								<th className="py-2">Type</th>
								<th className="py-2">Status</th>
								<th className="py-2">Last Fetched</th>
								<th className="py-2"></th>
							</tr>
						</thead>
						<tbody>
							{sources.map((source) => (
								<tr key={source.id} className="border-b">
									<td className="py-2">{source.name}</td>
									<td className="py-2">{source.type}</td>
									<td className="py-2">{source.status}</td>
									<td className="py-2">{source.last_fetched_at || 'Never'}</td>
									<td className="py-2 text-right">
										<button
											onClick={() => handle_delete(source.id)}
											className="text-red-600 hover:underline"
										>
											Delete
										</button>
									</td>
								</tr>
							))}
						</tbody>
					</table>
				)}
			</div>
		</div>
	);
};

export default Sources;
