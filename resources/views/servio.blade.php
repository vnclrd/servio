<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servio — Book Borrowing Management</title>
    @vite(['resources/css/servio.css', 'resources/js/servio.js'])
</head>
<body>
    <main class="container">
        <header class="header">
            <h1>Servio</h1>
            <p>Book Borrowing Management System</p>
        </header>

        <section class="controls">
          <input
              type="search"
              id="searchInput"
              placeholder="Search by title or author..."
              aria-label="Search books by title or author"
          >
          <button type="button" id="searchBtn">Search</button>
          <button type="button" id="clearSearchBtn">Clear</button>
      </section>

        <div id="feedbackBanner" class="feedback hidden" role="status" aria-live="polite"></div>

        <section class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="booksTableBody">
                    <tr>
                        <td colspan="5" class="empty-state">Loading books...</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>