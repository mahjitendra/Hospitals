<?php

/**
 * Pagination Class
 * 
 * Handles pagination logic and rendering
 */
class Pagination
{
    private $currentPage;
    private $totalItems;
    private $itemsPerPage;
    private $totalPages;
    private $baseUrl;
    private $queryParams;
    
    public function __construct($currentPage, $totalItems, $itemsPerPage = 25, $baseUrl = '', $queryParams = [])
    {
        $this->currentPage = max(1, (int) $currentPage);
        $this->totalItems = (int) $totalItems;
        $this->itemsPerPage = max(1, (int) $itemsPerPage);
        $this->totalPages = ceil($this->totalItems / $this->itemsPerPage);
        $this->baseUrl = $baseUrl;
        $this->queryParams = $queryParams;
    }
    
    /**
     * Get current page
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }
    
    /**
     * Get total pages
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }
    
    /**
     * Get total items
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }
    
    /**
     * Get items per page
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }
    
    /**
     * Get offset for database query
     */
    public function getOffset()
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }
    
    /**
     * Check if there is a previous page
     */
    public function hasPrevious()
    {
        return $this->currentPage > 1;
    }
    
    /**
     * Check if there is a next page
     */
    public function hasNext()
    {
        return $this->currentPage < $this->totalPages;
    }
    
    /**
     * Get previous page number
     */
    public function getPreviousPage()
    {
        return $this->hasPrevious() ? $this->currentPage - 1 : null;
    }
    
    /**
     * Get next page number
     */
    public function getNextPage()
    {
        return $this->hasNext() ? $this->currentPage + 1 : null;
    }
    
    /**
     * Get first page number
     */
    public function getFirstPage()
    {
        return 1;
    }
    
    /**
     * Get last page number
     */
    public function getLastPage()
    {
        return $this->totalPages;
    }
    
    /**
     * Get page range for display
     */
    public function getPageRange($range = 5)
    {
        $start = max(1, $this->currentPage - floor($range / 2));
        $end = min($this->totalPages, $start + $range - 1);
        
        // Adjust start if we're near the end
        if ($end - $start + 1 < $range) {
            $start = max(1, $end - $range + 1);
        }
        
        return range($start, $end);
    }
    
    /**
     * Generate URL for specific page
     */
    public function getUrl($page)
    {
        $params = array_merge($this->queryParams, ['page' => $page]);
        $queryString = http_build_query($params);
        
        return $this->baseUrl . ($queryString ? '?' . $queryString : '');
    }
    
    /**
     * Get pagination info text
     */
    public function getInfoText()
    {
        if ($this->totalItems == 0) {
            return 'No items found';
        }
        
        $start = ($this->currentPage - 1) * $this->itemsPerPage + 1;
        $end = min($this->currentPage * $this->itemsPerPage, $this->totalItems);
        
        return "Showing {$start} to {$end} of {$this->totalItems} items";
    }
    
    /**
     * Render pagination HTML
     */
    public function render($template = 'bootstrap')
    {
        if ($this->totalPages <= 1) {
            return '';
        }
        
        switch ($template) {
            case 'bootstrap':
                return $this->renderBootstrap();
            case 'simple':
                return $this->renderSimple();
            default:
                return $this->renderBootstrap();
        }
    }
    
    /**
     * Render Bootstrap pagination
     */
    private function renderBootstrap()
    {
        $html = '<nav aria-label="Page navigation">';
        $html .= '<ul class="pagination justify-content-center">';
        
        // Previous button
        if ($this->hasPrevious()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->getUrl($this->getPreviousPage()) . '" aria-label="Previous">';
            $html .= '<span aria-hidden="true">&laquo;</span>';
            $html .= '</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link" aria-label="Previous">';
            $html .= '<span aria-hidden="true">&laquo;</span>';
            $html .= '</span>';
            $html .= '</li>';
        }
        
        // First page
        if ($this->currentPage > 3) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->getUrl(1) . '">1</a>';
            $html .= '</li>';
            
            if ($this->currentPage > 4) {
                $html .= '<li class="page-item disabled">';
                $html .= '<span class="page-link">...</span>';
                $html .= '</li>';
            }
        }
        
        // Page numbers
        foreach ($this->getPageRange() as $page) {
            if ($page == $this->currentPage) {
                $html .= '<li class="page-item active">';
                $html .= '<span class="page-link">' . $page . '</span>';
                $html .= '</li>';
            } else {
                $html .= '<li class="page-item">';
                $html .= '<a class="page-link" href="' . $this->getUrl($page) . '">' . $page . '</a>';
                $html .= '</li>';
            }
        }
        
        // Last page
        if ($this->currentPage < $this->totalPages - 2) {
            if ($this->currentPage < $this->totalPages - 3) {
                $html .= '<li class="page-item disabled">';
                $html .= '<span class="page-link">...</span>';
                $html .= '</li>';
            }
            
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->getUrl($this->totalPages) . '">' . $this->totalPages . '</a>';
            $html .= '</li>';
        }
        
        // Next button
        if ($this->hasNext()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->getUrl($this->getNextPage()) . '" aria-label="Next">';
            $html .= '<span aria-hidden="true">&raquo;</span>';
            $html .= '</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link" aria-label="Next">';
            $html .= '<span aria-hidden="true">&raquo;</span>';
            $html .= '</span>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Render simple pagination
     */
    private function renderSimple()
    {
        $html = '<div class="pagination-simple">';
        
        if ($this->hasPrevious()) {
            $html .= '<a href="' . $this->getUrl($this->getPreviousPage()) . '" class="btn btn-secondary">Previous</a>';
        }
        
        $html .= '<span class="pagination-info">' . $this->getInfoText() . '</span>';
        
        if ($this->hasNext()) {
            $html .= '<a href="' . $this->getUrl($this->getNextPage()) . '" class="btn btn-secondary">Next</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Convert to array
     */
    public function toArray()
    {
        return [
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages,
            'total_items' => $this->totalItems,
            'items_per_page' => $this->itemsPerPage,
            'has_previous' => $this->hasPrevious(),
            'has_next' => $this->hasNext(),
            'previous_page' => $this->getPreviousPage(),
            'next_page' => $this->getNextPage(),
            'first_page' => $this->getFirstPage(),
            'last_page' => $this->getLastPage(),
            'page_range' => $this->getPageRange(),
            'info_text' => $this->getInfoText()
        ];
    }
    
    /**
     * Convert to JSON
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}