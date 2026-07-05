import React, { useState, useEffect } from 'react';

const Home = () => {
	const [posts, setPosts] = useState([]);
	const [currentPage, setCurrentPage] = useState(() => {
		const page_from_hash = window.location.hash.match(/#paged=(\d+)/);
		return page_from_hash ? parseInt(page_from_hash[1], 10) : 1;
	});

	const posts_per_page = 8;

	useEffect(() => {
		const fetch_data = async () => {
			spa_modal(true);
			try {
				// Dummy Posts (replaced codexpert.io)
				const response = await fetch(`https://jsonplaceholder.typicode.com/posts?_page=${currentPage}&_limit=${posts_per_page}`);
				const data = await response.json();

				const posts_with_thumbnails = await Promise.all(data.map(async (post) => {
					// Dummy Media / Thumbnail
					const thumbnailUrl = `https://picsum.photos/id/${post.id}/300/200`;
					return { 
						...post, 
						title: { rendered: post.title }, 
						link: "#", 
						thumbnail: thumbnailUrl 
					};
				}));

				setPosts(posts_with_thumbnails);
			} catch (error) {
				console.error('Error fetching posts:', error);
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
		setCurrentPage(currentPage + 1);
	};

	return (
		<div className="min-h-screen bg-gray-100 flex flex-col items-center py-8">
			<ol className="w-full max-w-2xl bg-white shadow-md rounded-lg p-6 mb-4">
				{posts.map((post, index) => (
					<li key={index} className="mb-4 border-b pb-4 flex">
						<img 
							src={post.thumbnail} 
							alt={post.title.rendered} 
							className="w-8 h-8 object-cover mr-4 rounded"
						/>
						<div>
							<a 
								href={post.link} 
								target="_blank" 
								className="text-blue-600 hover:underline" 
								dangerouslySetInnerHTML={{ __html: post.title.rendered }}
							></a>
						</div>
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
					className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700" 
					onClick={handle_next}
				>
					Next
				</button>
			</div>
		</div>
	);
}

export default Home;