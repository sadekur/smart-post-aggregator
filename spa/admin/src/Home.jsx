import React, { useState, useEffect } from 'react';
import {
	IconChartBar,
	IconCalendar,
	IconLayers,
	IconCheckCircle,
	IconInbox,
	IconAlertTriangle,
	IconExternalLink,
	IconImage,
} from './components/Icons';
import { PageShell, PageHeader, Card, StatTile, StatTileSkeleton, ListRowSkeleton, EmptyState, Pagination } from './components/UI';

const Home = () => {
	const [posts, setPosts] = useState([]);
	const [totalPages, setTotalPages] = useState(1);
	const [currentPage, setCurrentPage] = useState(() => {
		const page_from_hash = window.location.hash.match(/#paged=(\d+)/);
		return page_from_hash ? parseInt(page_from_hash[1], 10) : 1;
	});
	const [stats, setStats] = useState(null);
	const [statsLoading, setStatsLoading] = useState(true);
	const [loading, setLoading] = useState(true);

	const posts_per_page = 8;

	useEffect(() => {
		const fetch_stats = async () => {
			setStatsLoading(true);
			try {
				const response = await fetch(`${SPA_PLUGIN_ADMIN.api_base}/stats`, {
					headers: { 'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce },
				});
				const data = await response.json();
				setStats(data);
			} catch (error) {
				console.error('Error fetching stats:', error);
			} finally {
				setStatsLoading(false);
			}
		};

		fetch_stats();
	}, []);

	useEffect(() => {
		const fetch_data = async () => {
			setLoading(true);
			spa_modal(true);
			try {
				const url = `${SPA_PLUGIN_ADMIN.api_base}/content?page=${currentPage}&per_page=${posts_per_page}`;
				const response = await fetch(url, {
					headers: { 'X-WP-Nonce': SPA_PLUGIN_ADMIN.nonce },
				});

				const data = await response.json();

				setTotalPages(parseInt(response.headers.get('X-WP-TotalPages'), 10) || 1);

				setPosts(Array.isArray(data) ? data : []);
			} catch (error) {
				console.error('Error fetching aggregated content:', error);
				setPosts([]);
			} finally {
				setLoading(false);
				spa_modal(false);
			}
		};

		fetch_data();
	}, [currentPage]);

	useEffect(() => {
		window.location.hash = `#paged=${currentPage}`;
	}, [currentPage]);

	const handle_previous = () => {
		if (currentPage > 1) {
			setCurrentPage(currentPage - 1);
		}
	};

	const handle_next = () => {
		if (currentPage < totalPages) {
			setCurrentPage(currentPage + 1);
		}
	};

	return (
		<PageShell maxWidth="max-w-4xl">
			<PageHeader
				icon={IconChartBar}
				title="Dashboard"
				subtitle="Overview of aggregated content and duplicate-detection activity"
			/>

			{statsLoading && (
				<div className="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
					{Array.from({ length: 6 }).map((_, i) => (
						<StatTileSkeleton key={i} />
					))}
				</div>
			)}

			{!statsLoading && stats && (
				<div className="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
					<StatTile icon={IconCalendar} color="indigo" label="Aggregated Today" value={stats.aggregated_today} />
					<StatTile icon={IconChartBar} color="sky" label="Aggregated This Week" value={stats.aggregated_week} />
					<StatTile icon={IconLayers} color="violet" label="Total Aggregated" value={stats.aggregated_total} />
					<StatTile icon={IconCheckCircle} color="emerald" label="Auto-Resolved" value={stats.auto_resolved} />
					<StatTile
						icon={IconInbox}
						color="amber"
						label="Pending Review"
						value={stats.pending_review}
						highlight={stats.pending_review > 0}
					/>
					<StatTile
						icon={IconAlertTriangle}
						color="rose"
						label="Sources In Error"
						value={stats.sources_in_error}
						highlight={stats.sources_in_error > 0}
					/>
				</div>
			)}

			{statsLoading && (
				<Card title="Recent Activity" icon={IconAlertTriangle} className="mb-6">
					{Array.from({ length: 3 }).map((_, i) => (
						<ListRowSkeleton key={i} />
					))}
				</Card>
			)}

			{!statsLoading && stats && stats.recent_activity.length > 0 && (
				<Card title="Recent Activity" icon={IconAlertTriangle} className="mb-6">
					<ul className="-my-2">
						{stats.recent_activity.map((activity) => (
							<li key={activity.log_id} className="flex items-start gap-3 py-3 border-b border-gray-100 last:border-0">
								<span className="mt-1 inline-flex w-2 h-2 rounded-full bg-amber-400 shrink-0" />
								<p className="text-sm text-gray-600 leading-relaxed">
									<span className="font-medium text-gray-900" dangerouslySetInnerHTML={{ __html: activity.new_title }}></span>
									{activity.matched_title && (
										<>
											{' '}
											matched{' '}
											<span dangerouslySetInnerHTML={{ __html: activity.matched_title }}></span>
										</>
									)}{' '}
									&mdash; <span className="font-semibold text-gray-800">{activity.score}%</span> ({activity.resolution})
								</p>
							</li>
						))}
					</ul>
				</Card>
			)}

			<Card title="Latest Aggregated Content" icon={IconLayers} className="mb-6">
				{loading &&
					Array.from({ length: 5 }).map((_, i) => <ListRowSkeleton key={i} />)}

				{!loading && posts.length === 0 && (
					<EmptyState icon={IconLayers} title="No aggregated content yet" description="New items will show up here as sources are fetched." />
				)}

				{!loading && posts.length > 0 && (
					<ul className="-my-1">
						{posts.map((post) => (
							<li key={post.id} className="flex items-center gap-3 py-2.5 border-b border-gray-100 last:border-0">
								{post.thumbnail ? (
									<img src={post.thumbnail} alt="" className="w-9 h-9 object-cover rounded-lg ring-1 ring-gray-200 shrink-0" />
								) : (
									<span className="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-50 text-gray-300 shrink-0">
										<IconImage className="w-4 h-4" />
									</span>
								)}
								<a
									href={post.link}
									target="_blank"
									rel="noreferrer"
									className="flex-1 flex items-center gap-1.5 text-sm text-gray-700 hover:text-indigo-600 font-medium truncate"
									dangerouslySetInnerHTML={{ __html: post.title }}
								></a>
								<IconExternalLink className="w-3.5 h-3.5 text-gray-300 shrink-0" />
							</li>
						))}
					</ul>
				)}
			</Card>

			<Pagination currentPage={currentPage} totalPages={totalPages} onPrevious={handle_previous} onNext={handle_next} />
		</PageShell>
	);
};

export default Home;
