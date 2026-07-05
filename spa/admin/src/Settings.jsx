import React, { useState } from 'react';

const Settings = () => {
	const [setting1, setSetting1] = useState('');
	const [setting2, setSetting2] = useState(false);

	const handle_submit = ( event ) => {
		event.preventDefault();
		// Handle form submission logic here
		console.log('Settings saved:', { setting1, setting2 });
	};

	return (
		<div className="min-h-screen bg-gray-100 flex items-center justify-center py-8">
			<form className="bg-white p-6 rounded-lg shadow-md w-full max-w-lg" onSubmit={handle_submit}>
				<div className="mb-4">
					<label className="block text-gray-700 text-sm font-bold mb-2" htmlFor="setting1">
						Setting 1
					</label>
					<input
						id="setting1"
						type="text"
						className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
						value={setting1}
						onChange={( e ) => setSetting1(e.target.value)}
					/>
				</div>
				<div className="mb-4">
					<label className="block text-gray-700 text-sm font-bold mb-2" htmlFor="setting2">
						Setting 2
					</label>
					<input
						id="setting2"
						type="checkbox"
						className="mr-2 leading-tight"
						checked={setting2}
						onChange={( e ) => setSetting2(e.target.checked)}
					/>
					<span className="text-sm">Enable Setting 2</span>
				</div>
				<div className="flex items-center justify-between">
					<button
						type="submit"
						className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
					>
						Save Settings
					</button>
				</div>
			</form>
		</div>
	);
}

export default Settings;
