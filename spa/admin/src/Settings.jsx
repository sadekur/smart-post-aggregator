import React, { useState, useEffect } from 'react';

const Settings = () => {
	const [form, setForm] = useState({
		algorithm: 'jaccard',
		threshold: 50,
		review_margin: 10,
		default_resolution: 'mark',
	});
	const [loading, setLoading] = useState(true);
	const [saving, setSaving] = useState(false);
	const [message, setMessage] = useState('');
	const [error, setError] = useState('');

	useEffect(() => {
		const fetch_settings = async () => {
			try {
				const response = await fetch(`${SPA_PLUGIN_ADMIN.api_base}/settings`, {
					headers: { 'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce },
				});
				const data = await response.json();
				setForm((prev) => ({ ...prev, ...data }));
			} catch (err) {
				console.error('Error fetching settings:', err);
			} finally {
				setLoading(false);
			}
		};

		fetch_settings();
	}, []);

	const handle_change = (event) => {
		const { name, value } = event.target;
		setForm((prev) => ({ ...prev, [name]: value }));
	};

	const handle_submit = async (event) => {
		event.preventDefault();
		setSaving(true);
		setMessage('');
		setError('');

		try {
			const response = await fetch(`${SPA_PLUGIN_ADMIN.api_base}/settings`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce,
				},
				body: JSON.stringify({
					...form,
					threshold: parseFloat(form.threshold) || 0,
					review_margin: parseFloat(form.review_margin) || 0,
				}),
			});

			const data = await response.json();

			if (!response.ok) {
				setError(data.message || 'Could not save settings.');
				return;
			}

			setForm((prev) => ({ ...prev, ...data }));
			setMessage('Settings saved.');
		} catch (err) {
			console.error('Error saving settings:', err);
			setError('Could not save settings.');
		} finally {
			setSaving(false);
		}
	};

	if (loading) {
		return (
			<div className="min-h-screen bg-gray-100 flex items-center justify-center py-8">
				<p className="text-gray-500">Loading...</p>
			</div>
		);
	}

	return (
		<div className="min-h-screen bg-gray-100 flex items-center justify-center py-8">
			<form className="bg-white p-6 rounded-lg shadow-md w-full max-w-lg" onSubmit={handle_submit}>
				<h3 className="text-lg font-semibold mb-4">Duplicate Detection</h3>

				{message && <p className="text-green-600 mb-3">{message}</p>}
				{error && <p className="text-red-600 mb-3">{error}</p>}

				<div className="mb-4">
					<label className="block text-gray-700 text-sm font-bold mb-2" htmlFor="algorithm">
						Algorithm
					</label>
					<select
						id="algorithm"
						name="algorithm"
						value={form.algorithm}
						onChange={handle_change}
						className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
					>
						<option value="jaccard">Jaccard Similarity</option>
					</select>
				</div>

				<div className="mb-4">
					<label className="block text-gray-700 text-sm font-bold mb-2" htmlFor="threshold">
						Similarity Threshold (%)
					</label>
					<input
						id="threshold"
						name="threshold"
						type="number"
						min="0"
						max="100"
						value={form.threshold}
						onChange={handle_change}
						className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
					/>
					<p className="text-xs text-gray-500 mt-1">
						Posts scoring at or above this are treated as a possible duplicate.
					</p>
				</div>

				<div className="mb-4">
					<label className="block text-gray-700 text-sm font-bold mb-2" htmlFor="review_margin">
						Review Margin (%)
					</label>
					<input
						id="review_margin"
						name="review_margin"
						type="number"
						min="0"
						max="100"
						value={form.review_margin}
						onChange={handle_change}
						className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
					/>
					<p className="text-xs text-gray-500 mt-1">
						Scores between the threshold and threshold + margin go to manual Review instead of
						being auto-resolved.
					</p>
				</div>

				<div className="mb-4">
					<label className="block text-gray-700 text-sm font-bold mb-2" htmlFor="default_resolution">
						High-Confidence Duplicates
					</label>
					<select
						id="default_resolution"
						name="default_resolution"
						value={form.default_resolution}
						onChange={handle_change}
						className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
					>
						<option value="mark">Keep, flagged as duplicate</option>
						<option value="trash">Auto-trash (recoverable)</option>
					</select>
				</div>

				<div className="flex items-center justify-between">
					<button
						type="submit"
						disabled={saving}
						className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
					>
						{saving ? 'Saving...' : 'Save Settings'}
					</button>
				</div>
			</form>
		</div>
	);
};

export default Settings;
