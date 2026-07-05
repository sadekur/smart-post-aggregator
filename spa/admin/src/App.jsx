import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import Home from './Home';
import Help from './Help';
import Settings from './Settings';

const App = () => {
	const defaultTab = '/home';
	const [activeTab, setActiveTab] = useState(window.location.hash.replace('#', '') || defaultTab);

	useEffect(() => {
		const validTabs = ['/home', '/help', '/settings'];
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
			{activeTab === '/home' && <Home />}
			{activeTab === '/help' && <Help />}
			{activeTab === '/settings' && <Settings />}
		</div>
	);
}

// Assuming the container exists in your HTML file
const container = document.getElementById('smart-post-aggregantor_render');
const root = createRoot(container);
root.render(<App />);

export default App;
