import React, { useState, useEffect } from 'react';
import './Blogs.css';

function AllBlogs() {
  // State to store blog posts
  const [blogPosts, setBlogPosts] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  // State for new post form
  const [newPost, setNewPost] = useState({
    title: '',
    summary: '',
    content: '',
    status: 'published',
  });
  
  // State to control form visibility
  const [showPostForm, setShowPostForm] = useState(false);
  
  // Fetch blog posts from API
  useEffect(() => {
    const fetchPosts = async () => {
      setIsLoading(true);
      try {
        // Using the endpoint from your Laravel controller
        const response = await fetch('http://127.0.0.1:8080/api/blogs', {
          headers: {
            'Accept': 'application/json',
            // Include authorization if using token-based auth
            // 'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const responseData = await response.json();
        
        // Handle Laravel pagination structure
        const postsArray = responseData.data || responseData;
        
        setBlogPosts(Array.isArray(postsArray) ? postsArray : []);
        setError(null);
      } catch (err) {
        setError('Failed to fetch blog posts. Please try again later.');
        console.error('Error fetching posts:', err);
        setBlogPosts([]);
      } finally {
        setIsLoading(false);
      }
    };

    fetchPosts();
  }, []);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setNewPost({
      ...newPost,
      [name]: value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      // Create form data for multipart/form-data (needed for image uploads)
      const formData = new FormData();
      
      // Add all fields from newPost
      formData.append('title', newPost.title);
      formData.append('content', newPost.content);
      formData.append('summary', newPost.summary);
      formData.append('status', 'published');
      
      // Using the correct endpoint from your Laravel controller
      const response = await fetch('http://127.0.0.1:8080/api/blogs', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          // Don't set Content-Type when using FormData, it will be set automatically
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: formData
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        console.error('Server error:', errorData);
        throw new Error(errorData.message || `HTTP error! Status: ${response.status}`);
      }
      
      const result = await response.json();
      
      // Add new post to the beginning of the array - it will be in result.blog
      const createdPost = result.blog;
      setBlogPosts(prevPosts => [createdPost, ...prevPosts]);
      
      // Reset form
      setNewPost({
        title: '',
        summary: '',
        content: '',
        image_url:'',
        status: 'published',
      });
      
      // Hide form after submission
      setShowPostForm(false);
    } catch (err) {
      console.error('Error creating post:', err);
      alert('Failed to create post. Please try again.');
    }
  };

  // Format date to be more readable
  const formatDate = (dateString) => {
    if (!dateString) return 'No date';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
  };

  return (
    <section className="blogs">
      <div className="blogs-container">
        <div className="blog-header">
          <h2>Latest Blog Posts</h2>
          <button 
            className="create-post-btn"
            onClick={() => setShowPostForm(!showPostForm)}
          >
            {showPostForm ? 'Cancel' : 'Create Post'}
          </button>
        </div>

        {showPostForm && (
          <div className="post-form-container">
            <h3>Create a New Post</h3>
            <form onSubmit={handleSubmit} className="post-form">
              <div className="form-group">
                <label htmlFor="title">Title</label>
                <input
                  type="text"
                  id="title"
                  name="title"
                  value={newPost.title}
                  onChange={handleInputChange}
                  required
                />
              </div>
              
              <div className="form-group">
                <label htmlFor="summary">Summary</label>
                <input
                  type="text"
                  id="summary"
                  name="summary"
                  value={newPost.summary}
                  onChange={handleInputChange}
                  required
                />
              </div>

              <div className="form-group">
                <label htmlFor="image">Upload Image</label>
                <input
                  type="file"
                  id="image"
                  name="image"
                  value={newPost.image_url}
                  onChange={handleInputChange}
                />
              </div>
              
              <div className="form-group">
                <label htmlFor="content">Content</label>
                <textarea
                  id="content"
                  name="content"
                  value={newPost.content}
                  onChange={handleInputChange}
                  rows="5"
                  required
                />
              </div>
              
              <button type="submit" className="submit-btn">Publish Post</button>
            </form>
          </div>
        )}

        {isLoading && <div className="loading">Loading posts...</div>}
        
        {error && <div className="error-message">{error}</div>}

        {!isLoading && !error && (
          <div className="blog-list">
            {!Array.isArray(blogPosts) ? (
              <div className="error-message">Invalid data format received from server.</div>
            ) : blogPosts.length === 0 ? (
              <p>No blog posts found.</p>
            ) : (
              blogPosts.map((post, index) => (
                <div key={post?.id || index} className="blog-card">
                  <div className="blog-image">
                    <img 
                      src={post?.image_url || `https://source.unsplash.com/random/800x600?blog`} 
                      alt={post?.title || 'Blog post'} 
                    />
                  </div>
                  <div className="blog-content">
                    <h3>{post?.title || 'Untitled'}</h3>
                    <div className="blog-meta">
                      <span className="blog-author">
                        By {post?.author?.name || post?.author?.username || 'Anonymous'}
                      </span>
                      <span className="blog-date">{formatDate(post?.created_at)}</span>
                    </div>
                    <p className="blog-summary">
                      {post?.summary || 'No summary available'}
                    </p>
                    <div className="blog-footer">
                      <button className="read-more-btn">Read More</button>
                      <div className="blog-stats">
                        <span className="blog-likes">
                          ❤️ {post?.likes_count || 0}
                        </span>
                        <span className="blog-comments">
                          💬 {post?.comments_count || 0}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>
        )}
      </div>
    </section>
  );
}

export default AllBlogs;