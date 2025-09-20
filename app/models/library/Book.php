<?php

/**
 * Book Model
 * 
 * Handles library book data operations
 */
class Book extends Model
{
    protected $table = 'books';
    protected $fillable = [
        'isbn', 'title', 'author_id', 'publisher_id', 'category_id',
        'edition', 'publication_year', 'pages', 'language', 'price',
        'location', 'rack_number', 'total_copies', 'available_copies',
        'description', 'keywords', 'cover_image', 'digital_copy',
        'status'
    ];
    
    /**
     * Find book by ISBN
     */
    public function findByISBN($isbn)
    {
        return $this->findBy('isbn', $isbn);
    }
    
    /**
     * Get books by category
     */
    public function getByCategory($categoryId)
    {
        return $this->where('category_id = :category_id AND status = :status', [
            'category_id' => $categoryId,
            'status' => BOOK_AVAILABLE
        ], 'title ASC');
    }
    
    /**
     * Get books by author
     */
    public function getByAuthor($authorId)
    {
        return $this->where('author_id = :author_id AND status = :status', [
            'author_id' => $authorId,
            'status' => BOOK_AVAILABLE
        ], 'title ASC');
    }
    
    /**
     * Get books by publisher
     */
    public function getByPublisher($publisherId)
    {
        return $this->where('publisher_id = :publisher_id AND status = :status', [
            'publisher_id' => $publisherId,
            'status' => BOOK_AVAILABLE
        ], 'title ASC');
    }
    
    /**
     * Search books
     */
    public function search($query, $limit = 20)
    {
        $sql = "SELECT b.*, a.author_name, p.publisher_name, c.category_name
                FROM {$this->table} b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN publishers p ON b.publisher_id = p.id
                LEFT JOIN categories c ON b.category_id = c.id
                WHERE (b.title LIKE :query OR b.isbn LIKE :query OR a.author_name LIKE :query)
                AND b.status != 'deleted'
                ORDER BY b.title ASC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, ['query' => "%{$query}%"]);
    }
    
    /**
     * Get book with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT b.*, 
                       a.author_name, a.biography as author_biography,
                       p.publisher_name, p.address as publisher_address,
                       c.category_name, c.description as category_description
                FROM {$this->table} b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN publishers p ON b.publisher_id = p.id
                LEFT JOIN categories c ON b.category_id = c.id
                WHERE b.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Check book availability
     */
    public function isAvailable($bookId)
    {
        $book = $this->find($bookId);
        return $book && $book['available_copies'] > 0 && $book['status'] === BOOK_AVAILABLE;
    }
    
    /**
     * Issue book
     */
    public function issueBook($bookId)
    {
        $book = $this->find($bookId);
        if (!$book || $book['available_copies'] <= 0) {
            throw new Exception('Book not available for issue');
        }
        
        return $this->update($bookId, [
            'available_copies' => $book['available_copies'] - 1
        ]);
    }
    
    /**
     * Return book
     */
    public function returnBook($bookId)
    {
        $book = $this->find($bookId);
        if (!$book) {
            throw new Exception('Book not found');
        }
        
        return $this->update($bookId, [
            'available_copies' => $book['available_copies'] + 1
        ]);
    }
    
    /**
     * Get book issues
     */
    public function getIssues($bookId)
    {
        $issueModel = new Issue();
        return $issueModel->where('book_id = :book_id', ['book_id' => $bookId], 'issue_date DESC');
    }
    
    /**
     * Get current issues
     */
    public function getCurrentIssues($bookId)
    {
        $issueModel = new Issue();
        return $issueModel->where('book_id = :book_id AND return_date IS NULL', ['book_id' => $bookId]);
    }
    
    /**
     * Get popular books
     */
    public function getPopularBooks($limit = 10)
    {
        $sql = "SELECT b.*, COUNT(i.id) as issue_count
                FROM {$this->table} b
                LEFT JOIN issues i ON b.id = i.book_id
                WHERE b.status = 'available'
                GROUP BY b.id
                ORDER BY issue_count DESC, b.title ASC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get new arrivals
     */
    public function getNewArrivals($limit = 10)
    {
        return $this->where('status = :status', ['status' => BOOK_AVAILABLE], 'created_at DESC', $limit);
    }
    
    /**
     * Get books by location
     */
    public function getByLocation($location)
    {
        return $this->where('location = :location AND status = :status', [
            'location' => $location,
            'status' => BOOK_AVAILABLE
        ], 'rack_number ASC, title ASC');
    }
    
    /**
     * Get library statistics
     */
    public function getLibraryStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_books,
                    SUM(total_copies) as total_copies,
                    SUM(available_copies) as available_copies,
                    SUM(total_copies - available_copies) as issued_copies,
                    COUNT(DISTINCT category_id) as total_categories,
                    COUNT(DISTINCT author_id) as total_authors,
                    COUNT(DISTINCT publisher_id) as total_publishers,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_books,
                    SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_books,
                    SUM(CASE WHEN status = 'damaged' THEN 1 ELSE 0 END) as damaged_books
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get books requiring attention
     */
    public function getBooksRequiringAttention()
    {
        // Books with low stock, overdue returns, etc.
        $sql = "SELECT b.*, 
                       CASE 
                           WHEN b.available_copies = 0 THEN 'out_of_stock'
                           WHEN b.available_copies <= 2 THEN 'low_stock'
                           ELSE 'normal'
                       END as stock_status
                FROM {$this->table} b
                WHERE b.available_copies <= 2 AND b.status = 'available'
                ORDER BY b.available_copies ASC, b.title ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Update book stock
     */
    public function updateStock($bookId, $totalCopies)
    {
        $book = $this->find($bookId);
        if (!$book) {
            throw new Exception('Book not found');
        }
        
        $issuedCopies = $book['total_copies'] - $book['available_copies'];
        $newAvailable = $totalCopies - $issuedCopies;
        
        if ($newAvailable < 0) {
            throw new Exception('Cannot reduce stock below issued copies');
        }
        
        return $this->update($bookId, [
            'total_copies' => $totalCopies,
            'available_copies' => $newAvailable
        ]);
    }
}