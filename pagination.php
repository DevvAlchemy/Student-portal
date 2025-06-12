<?php
/**
 * Pagination Class
 * 1.0
 */
// add to index later'

class Pagination {
    private $totalItems;      // Total number of items
    private $itemsPerPage;    // Items to show per page
    private $currentPage;     // Current page number
    private $totalPages;      // Total number of pages
    private $offset;          // SQL LIMIT offset
    private $range = 2;       // Pages to show on each side of current page
    
    // constructor
    public function __construct($totalItems, $itemsPerPage = 10, $currentPage = 1) {
        $this->totalItems = max(0, (int)$totalItems);
        $this->itemsPerPage = max(1, (int)$itemsPerPage);
        
        // Calculate total pages
        $this->totalPages = ceil($this->totalItems / $this->itemsPerPage);
        
        // Validate and set current page
        $this->currentPage = max(1, min($currentPage, $this->totalPages));
        
        // Calculate offset for SQL LIMIT
        $this->offset = ($this->currentPage - 1) * $this->itemsPerPage;
    }
    
    // Getting SQL LIMIT claus
    public function getSqlLimit() {
        return "LIMIT {$this->offset}, {$this->itemsPerPage}";
    }
    
    /**
     * Get offset value for manual SQL building
     * 
     * @return int Offset value
     */
    public function getOffset() {
        return $this->offset;
    }
    
    // Get items per page
     
    public function getLimit() {
        return $this->itemsPerPage;
    }
    
    // Check if pagination is needed
     
    public function hasPages() {
        return $this->totalPages > 1;
    }
    
    // Get pagination data for rendering
    public function getPaginationData() {
        return [
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages,
            'total_items' => $this->totalItems,
            'items_per_page' => $this->itemsPerPage,
            'offset' => $this->offset,
            'has_previous' => $this->currentPage > 1,
            'has_next' => $this->currentPage < $this->totalPages,
            'previous_page' => max(1, $this->currentPage - 1),
            'next_page' => min($this->totalPages, $this->currentPage + 1),
            'from_item' => $this->offset + 1,
            'to_item' => min($this->totalItems, $this->offset + $this->itemsPerPage)
        ];
    }
    
    /**
     * Generate page numbers array for display
     * Shows: 1 ... 4 5 [6] 7 8 ... 10
     */
    public function getPageNumbers() {
        $pages = [];
        
        // If total pages is small, show all
        if ($this->totalPages <= ($this->range * 2) + 3) {
            for ($i = 1; $i <= $this->totalPages; $i++) {
                $pages[] = $i;
            }
            return $pages;
        }
        
        // Always show first page
        $pages[] = 1;
        
        // Calculate start and end of range around current page
        $start = max(2, $this->currentPage - $this->range);
        $end = min($this->totalPages - 1, $this->currentPage + $this->range);
        
        // Add ellipsis if needed
        if ($start > 2) {
            $pages[] = '...';
        }
        
        // Add pages in range
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }
        
        // Add ellipsis if needed
        if ($end < $this->totalPages - 1) {
            $pages[] = '...';
        }
        
        // Always show last page
        if ($this->totalPages > 1) {
            $pages[] = $this->totalPages;
        }
        
        return $pages;
    }
    
    /**
     * Generate URL for a specific page
     */
    public function getPageUrl($page, $extraParams = []) {
        // Get current URL parameters
        $params = $_GET;
        
        // Update page parameter
        $params['page'] = $page;
        
        // Add any extra parameters
        foreach ($extraParams as $key => $value) {
            $params[$key] = $value;
        }
        
        // Remove page parameter if it's page 1 (cleaner URLs)
        if ($params['page'] == 1) {
            unset($params['page']);
        }
        
        // Build query string
        $queryString = http_build_query($params);
        
        // Get current script name
        $scriptName = $_SERVER['SCRIPT_NAME'];
        
        return $scriptName . ($queryString ? '?' . $queryString : '');
    }
    
    /**
     * Render pagination HTML
     * 
     * @return string HTML for pagination controls
     */
    public function render() {
        if (!$this->hasPages()) {
            return '';
        }
        
        $data = $this->getPaginationData();
        $pages = $this->getPageNumbers();
        
        $html = '<div class="pagination-container">';
        $html .= '<div class="pagination-info">';
        $html .= "Showing {$data['from_item']} to {$data['to_item']} of {$data['total_items']} entries";
        $html .= '</div>';
        
        $html .= '<nav class="pagination">';
        
        // Previous button
        if ($data['has_previous']) {
            $html .= '<a href="' . $this->getPageUrl($data['previous_page']) . '" class="pagination-btn">← Previous</a>';
        } else {
            $html .= '<span class="pagination-btn disabled">← Previous</span>';
        }
        
        // Page numbers
        $html .= '<div class="pagination-numbers">';
        foreach ($pages as $page) {
            if ($page === '...') {
                $html .= '<span class="pagination-ellipsis">...</span>';
            } elseif ($page == $this->currentPage) {
                $html .= '<span class="pagination-number active">' . $page . '</span>';
            } else {
                $html .= '<a href="' . $this->getPageUrl($page) . '" class="pagination-number">' . $page . '</a>';
            }
        }
        $html .= '</div>';
        
        // Next button
        if ($data['has_next']) {
            $html .= '<a href="' . $this->getPageUrl($data['next_page']) . '" class="pagination-btn">Next →</a>';
        } else {
            $html .= '<span class="pagination-btn disabled">Next →</span>';
        }
        
        $html .= '</nav>';
        $html .= '</div>';
        
        return $html;
    }
}
?>