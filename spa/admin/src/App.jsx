import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import Home from './Home';
import Sources from './Sources';
import Review from './Review';
import Logs from './Logs';
import Settings from './Settings';

const VALID_TABS = ['/home', '/sources', '/review', '/logs', '/settings'];

/**
 * The wp-admin sidebar submenu items (Dashboard/Sources/Review/Logs/Settings)
 * all point at the exact same `?page=smart-post-aggregator` URL — only their
 * `#/...` fragment differs, and fragments never reach the server. So WP's
 * own "current page" detection (which only ever sees `?page=...`) always
 * marks the same one (Dashboard) as active, regardless of which tab is
 * actually showing. This re-derives the correct highlight client-side by
 * comparing each submenu link's fragment against the active tab.
 */
const syncSidebarHighlight = (activeTab) => {
	const links = document.querySelectorAll('#adminmenu .wp-submenu a[href*="page=smart-post-aggregator"]');

	links.forEach((link) => {
		const li = link.closest('li');
		if (!li) {
			return;
		}

		const hashIndex = link.getAttribute('href').indexOf('#');
		const linkTab = hashIndex === -1 ? '/home' : link.getAttribute('href').slice(hashIndex + 1) || '/home';

		li.classList.toggle('current', linkTab === activeTab);
	});
};

const App = () => {
	const defaultTab = '/home';
	const [activeTab, setActiveTab] = useState(window.location.hash.replace('#', '') || defaultTab);

	useEffect(() => {
		const handleHashChange = () => {
			const newHash = window.location.hash.replace('#', '');
			if (VALID_TABS.includes(newHash)) {
				setActiveTab(newHash);
			} else {
				setActiveTab(defaultTab);
				window.location.hash = '';
			}
		};

		window.addEventListener('hashchange', handleHashChange);

		// Initial check to ensure correct tab is set on page load. Navigating
		// here via a wp-admin sidebar submenu link (e.g. #/sources) already
		// has the hash set before this app ever mounts.
		handleHashChange();

		return () => {
			window.removeEventListener('hashchange', handleHashChange);
		};
	}, []);

	useEffect(() => {
		syncSidebarHighlight(activeTab);
	}, [activeTab]);

	return (
		<div className="min-h-screen bg-gray-100">
			{activeTab === '/home' && <Home />}
			{activeTab === '/sources' && <Sources />}
			{activeTab === '/review' && <Review />}
			{activeTab === '/logs' && <Logs />}
			{activeTab === '/settings' && <Settings />}
		</div>
	);
}

// Assuming the container exists in your HTML file
const container = document.getElementById('smart-post-aggregator_render');
const root = createRoot(container);
root.render(<App />);

export default App;
