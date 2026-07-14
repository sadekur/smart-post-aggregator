import React, { useState, useEffect } from 'react';
import { IconSliders } from './components/Icons';
import { PageShell, PageHeader, Card, Button, Alert, LoadingState } from './components/UI';

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

	return (
		<PageShell maxWidth="max-w-2xl">
			<PageHeader icon={IconSliders} title="Settings" subtitle="Tune how aggressively duplicate content gets flagged" />

			<Card>
				{loading && <LoadingState label="Loading settings…" />}

				{!loading && (
					<form onSubmit={handle_submit} className="space-y-6">
						{message && <Alert type="success">{message}</Alert>}
						{error && <Alert type="error">{error}</Alert>}

						<div>
							<label className="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5" htmlFor="algorithm">
								Algorithm
							</label>
							<select
								id="algorithm"
								name="algorithm"
								value={form.algorithm}
								onChange={handle_change}
								className="w-full rounded-lg border border-gray-300 py-2.5 px-3 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none"
							>
								<option value="jaccard">Jaccard Similarity</option>
							</select>
						</div>

						<div>
							<div className="flex items-center justify-between mb-1.5">
								<label className="text-xs font-semibold uppercase tracking-wide text-gray-500" htmlFor="threshold">
									Similarity Threshold
								</label>
								<span className="text-sm font-bold text-indigo-600">{form.threshold}%</span>
							</div>
							<div className="flex items-center gap-3">
								<input
									id="threshold"
									name="threshold"
									type="range"
									min="0"
									max="100"
									value={form.threshold}
									onChange={handle_change}
									className="flex-1 accent-indigo-600"
								/>
								<input
									name="threshold"
									type="number"
									min="0"
									max="100"
									value={form.threshold}
									onChange={handle_change}
									className="w-16 rounded-lg border border-gray-300 py-1.5 px-2 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none"
								/>
							</div>
							<p className="text-xs text-gray-400 mt-1.5">Posts scoring at or above this are treated as a possible duplicate.</p>
						</div>

						<div>
							<div className="flex items-center justify-between mb-1.5">
								<label className="text-xs font-semibold uppercase tracking-wide text-gray-500" htmlFor="review_margin">
									Review Margin
								</label>
								<span className="text-sm font-bold text-indigo-600">{form.review_margin}%</span>
							</div>
							<div className="flex items-center gap-3">
								<input
									id="review_margin"
									name="review_margin"
									type="range"
									min="0"
									max="100"
									value={form.review_margin}
									onChange={handle_change}
									className="flex-1 accent-indigo-600"
								/>
								<input
									name="review_margin"
									type="number"
									min="0"
									max="100"
									value={form.review_margin}
									onChange={handle_change}
									className="w-16 rounded-lg border border-gray-300 py-1.5 px-2 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none"
								/>
							</div>
							<p className="text-xs text-gray-400 mt-1.5">
								Scores between the threshold and threshold + margin go to manual Review instead of being auto-resolved.
							</p>
						</div>

						<div>
							<label className="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5" htmlFor="default_resolution">
								High-Confidence Duplicates
							</label>
							<select
								id="default_resolution"
								name="default_resolution"
								value={form.default_resolution}
								onChange={handle_change}
								className="w-full rounded-lg border border-gray-300 py-2.5 px-3 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none"
							>
								<option value="mark">Keep, flagged as duplicate</option>
								<option value="trash">Auto-trash (recoverable)</option>
							</select>
						</div>

						<div className="pt-2 border-t border-gray-100">
							<Button type="submit" disabled={saving}>
								{saving ? 'Saving…' : 'Save Settings'}
							</Button>
						</div>
					</form>
				)}
			</Card>
		</PageShell>
	);
};

export default Settings;
