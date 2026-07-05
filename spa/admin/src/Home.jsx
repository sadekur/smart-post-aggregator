import React, { useState, useEffect } from 'react';

const Home = () => {
	const [posts, setPosts] = useState([]);
	const [totalPages, setTotalPages] = useState(1);
	const [currentPage, setCurrentPage] = useState(() => {
		const page_from_hash = window.location.hash.match(/#paged=(\d+)/);
		return page_from_hash ? parseInt(page_from_hash[1], 10) : 1;
	});

	const posts_per_page = 8;

	useEffect(() => {
		const fetch_data = async () => {
			spa_modal(true);
			try {
				const url = `${spa_PLUGIN_ADMIN.rest_root}wp/v2/spa_content?page=${currentPage}&per_page=${posts_per_page}&_embed`;
				const response = await fetch(url, {
					headers: { 'X-WP-Nonce': spa_PLUGIN_ADMIN.nonce },
				});

				const data = await response.json();

				setTotalPages(parseInt(response.headers.get('X-WP-TotalPages'), 10) || 1);

				const normalized = Array.isArray(data) ? data.map((post) => ({
					id: post.id,
					title: post.title?.rendered ?? '',
					link: post.link,
					thumbnail: post._embedded?.['wp:featuredmedia']?.[0]?.source_url ?? null,
				})) : [];

				setPosts(normalized);
			} catch (error) {
				console.error('Error fetching aggregated content:', error);
				setPosts([]);
			} finally {
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
		<div className="min-h-screen bg-gray-100 flex flex-col items-center py-8">
			<ol className="w-full max-w-2xl bg-white shadow-md rounded-lg p-6 mb-4">
				{posts.length === 0 && (
					<li className="text-gray-500 text-center py-4">No aggregated content yet.</li>
				)}
				{posts.map((post) => (
					<li key={post.id} className="mb-4 border-b pb-4 flex items-center">
						{post.thumbnail && (
							<img
								src={post.thumbnail}
								alt={post.title}
								className="w-8 h-8 object-cover mr-4 rounded"
							/>
						)}
						<a
							href={post.link}
							target="_blank"
							rel="noreferrer"
							className="text-blue-600 hover:underline"
							dangerouslySetInnerHTML={{ __html: post.title }}
						></a>
					</li>
				))}
			</ol>

			<div className="flex justify-between w-full max-w-2xl">
				<button
					className="px-4 py-2 bg-gray-300 text-gray-700 rounded disabled:opacity-50"
					onClick={handle_previous}
					disabled={currentPage === 1}
				>
					Previous
				</button>
				<button
					className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700 disabled:opacity-50"
					onClick={handle_next}
					disabled={currentPage >= totalPages}
				>
					Next
				</button>
			</div>
		</div>
	);
}

export default Home;
