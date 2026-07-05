import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import Home from './Home';
import Sources from './Sources';
import Help from './Help';
import Settings from './Settings';

const TABS = [
	{ hash: '/home', label: 'Dashboard' },
	{ hash: '/sources', label: 'Sources' },
	{ hash: '/settings', label: 'Settings' },
	{ hash: '/help', label: 'Help' },
];

const App = () => {
	const defaultTab = '/home';
	const [activeTab, setActiveTab] = useState(window.location.hash.replace('#', '') || defaultTab);

	useEffect(() => {
		const validTabs = TABS.map((tab) => tab.hash);
		const handleHashChange = () => {
			const newHash = window.location.hash.replace('#', '');
			if (validTabs.includes(newHash)) {
				setActiveTab(newHash);
			} else {
				setActiveTab(defaultTab);
				window.location.hash = '';
			}
		};

		window.addEventListener('hashchange', handleHashChange);

		// Initial check to ensure correct tab is set on page load
		handleHashChange();

		return () => {
			window.removeEventListener('hashchange', handleHashChange);
		};
	}, []);

	return (
		<div className="min-h-screen bg-gray-100">
			<nav className="bg-white shadow-md flex gap-2 px-6 py-3 mb-4">
				{TABS.map((tab) => (
					<a
						key={tab.hash}
						href={`#${tab.hash}`}
						className={`px-3 py-2 rounded text-sm font-medium ${
							activeTab === tab.hash ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-200'
						}`}
					>
						{tab.label}
					</a>
				))}
			</nav>

			{activeTab === '/home' && <Home />}
			{activeTab === '/sources' && <Sources />}
			{activeTab === '/help' && <Help />}
			{activeTab === '/settings' && <Settings />}
		</div>
	);
}

// Assuming the container exists in your HTML file
const container = document.getElementById('smart-post-aggregator_render');
const root = createRoot(container);
root.render(<App />);

export default App;
