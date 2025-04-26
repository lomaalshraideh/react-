import React, { useState, useEffect } from 'react';
import './Blogs.css';

function Blogs() {
  // State to store blog posts
  const [blogPosts, setBlogPosts] = useState([
    {
      id: 1,
      title: "Getting Started with React Hooks",
      author: "Jane Smith",
      date: "April 22, 2025",
      summary: "Learn how to use React Hooks to simplify your functional components and manage state effectively.",
      content: "React Hooks are a powerful addition to the React library that allows you to use state and other React features without writing a class...",
      image: "https://images.unsplash.com/photo-1633356122544-f134324a6cee?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80",
      likes: 45,
      comments: 12
    },
    {
      id: 2,
      title: "Building Responsive UIs with CSS Grid",
      author: "Mike Johnson",
      date: "April 18, 2025",
      summary: "Discover how to create beautiful responsive layouts using modern CSS Grid techniques.",
      content: "CSS Grid has revolutionized how we build layouts on the web, providing a two-dimensional system that handles both columns and rows...",
      image: "https://images.unsplash.com/photo-1517134191118-9d595e4c8c2b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80",
      likes: 32,
      comments: 8
    },
    {
      id: 3,
      title: "JavaScript Promises Explained",
      author: "Sarah Williams",
      date: "April 15, 2025",
      summary: "A comprehensive guide to understanding and working with Promises in JavaScript.",
      content: "Promises in JavaScript represent the eventual completion (or failure) of an asynchronous operation and its resulting value...",
      image: "https://images.unsplash.com/photo-1587620962725-abab7fe55159?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80",
      likes: 67,
      comments: 21
    },
  ]);

  // State for new post form
  const [newPost, setNewPost] = useState({
    title: '',
    summary: '',
    content: '',
  });
  
  // State to control form visibility
  const [showPostForm, setShowPostForm] = useState(false);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setNewPost({
      ...newPost,
      [name]: value
    });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    
    // Create new post with generated data
    const post = {
      id: blogPosts.length + 1,
      title: newPost.title,
      author: "Current User", // In a real app, this would come from authentication
      date: new Date().toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'}),
      summary: newPost.summary,
      content: newPost.content,
      image: `https://source.unsplash.com/random/800x600?${newPost.title.split(' ')[0].toLowerCase()}`,
      likes: 0,
      comments: 0
    };
    
    // Add new post to the beginning of the array
    setBlogPosts([post, ...blogPosts]);
    
    // Reset form
    setNewPost({
      title: '',
      summary: '',
      content: '',
    });
    
    // Hide form after submission
    setShowPostForm(false);
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

        <div className="blog-list">
          {blogPosts.map((post) => (
            <div key={post.id} className="blog-card">
              <div className="blog-image">
                <img src={post.image} alt={post.title} />
              </div>
              <div className="blog-content">
                <h3>{post.title}</h3>
                <div className="blog-meta">
                  <span className="blog-author">By {post.author}</span>
                  <span className="blog-date">{post.date}</span>
                </div>
                <p className="blog-summary">{post.summary}</p>
                <div className="blog-footer">
                  <button className="read-more-btn">Read More</button>
                  <div className="blog-stats">
                    <span className="blog-likes">❤️ {post.likes}</span>
                    <span className="blog-comments">💬 {post.comments}</span>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

export default Blogs;
