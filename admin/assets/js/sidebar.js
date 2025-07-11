/**
 * Unified Sidebar JavaScript
 * Handles sidebar interactions, responsive behavior, and state management
 */

class SidebarManager {
    constructor() {
        this.sidebar = document.getElementById('sidebar');
        this.sidebarToggle = document.getElementById('sidebarToggle');
        this.sidebarOverlay = document.getElementById('sidebarOverlay');
        this.mainContent = document.querySelector('.main-content');
        
        this.isCollapsed = false;
        this.isMobile = false;
        this.isVisible = false;
        
        this.init();
    }
    
    init() {
        this.checkMobile();
        this.loadSavedState();
        this.bindEvents();
        this.initDropdowns();
        this.setActiveMenu();
        
        // Initialize on page load
        window.addEventListener('load', () => {
            this.handleResize();
        });
    }
    
    checkMobile() {
        this.isMobile = window.innerWidth <= 1024;
        
        if (this.isMobile) {
            this.sidebar.classList.remove('collapsed');
            this.isCollapsed = false;
        }
    }
    
    loadSavedState() {
        if (!this.isMobile) {
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                this.collapse();
            }
        }
    }
    
    bindEvents() {
        // Toggle button
        if (this.sidebarToggle) {
            this.sidebarToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggle();
            });
        }
        
        // Overlay click (mobile)
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', () => {
                this.hide();
            });
        }
        
        // Window resize
        window.addEventListener('resize', () => {
            this.handleResize();
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            this.handleKeyboard(e);
        });
        
        // Dropdown toggles
        const dropdownLinks = document.querySelectorAll('.nav-link[data-toggle="dropdown"]');
        dropdownLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleDropdown(link);
            });
        });
    }
    
    initDropdowns() {
        // Auto-expand active dropdowns
        const activeDropdowns = document.querySelectorAll('.nav-dropdown.show');
        activeDropdowns.forEach(dropdown => {
            const parentLink = dropdown.previousElementSibling;
            if (parentLink) {
                parentLink.classList.add('active');
            }
        });
    }
    
    setActiveMenu() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link, .nav-dropdown-link');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href !== '#' && href !== 'javascript:void(0)') {
                if (currentPath.includes(href) || currentPath === href) {
                    link.classList.add('active');
                    
                    // If it's a dropdown item, expand its parent
                    if (link.classList.contains('nav-dropdown-link')) {
                        const dropdown = link.closest('.nav-dropdown');
                        if (dropdown) {
                            dropdown.classList.add('show');
                            const parentLink = dropdown.previousElementSibling;
                            if (parentLink) {
                                parentLink.classList.add('active');
                            }
                        }
                    }
                }
            }
        });
    }
    
    toggle() {
        if (this.isMobile) {
            this.isVisible ? this.hide() : this.show();
        } else {
            this.isCollapsed ? this.expand() : this.collapse();
        }
    }
    
    collapse() {
        if (this.isMobile) return;
        
        this.sidebar.classList.add('collapsed');
        this.isCollapsed = true;
        
        // Close all dropdowns when collapsing
        const dropdowns = document.querySelectorAll('.nav-dropdown.show');
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('show');
        });
        
        this.saveState();
        this.triggerEvent('collapsed');
    }
    
    expand() {
        if (this.isMobile) return;
        
        this.sidebar.classList.remove('collapsed');
        this.isCollapsed = false;
        
        // Re-expand active dropdowns
        this.initDropdowns();
        
        this.saveState();
        this.triggerEvent('expanded');
    }
    
    show() {
        if (!this.isMobile) return;
        
        this.sidebar.classList.add('show');
        this.sidebarOverlay.classList.add('show');
        this.isVisible = true;
        
        // Prevent body scrolling
        document.body.style.overflow = 'hidden';
        
        this.triggerEvent('shown');
    }
    
    hide() {
        if (!this.isMobile) return;
        
        this.sidebar.classList.remove('show');
        this.sidebarOverlay.classList.remove('show');
        this.isVisible = false;
        
        // Restore body scrolling
        document.body.style.overflow = '';
        
        this.triggerEvent('hidden');
    }
    
    toggleDropdown(link) {
        const dropdown = link.nextElementSibling;
        if (!dropdown || !dropdown.classList.contains('nav-dropdown')) return;
        
        const isOpen = dropdown.classList.contains('show');
        
        // Close other dropdowns (accordion behavior)
        const otherDropdowns = document.querySelectorAll('.nav-dropdown.show');
        otherDropdowns.forEach(otherDropdown => {
            if (otherDropdown !== dropdown) {
                otherDropdown.classList.remove('show');
                const otherLink = otherDropdown.previousElementSibling;
                if (otherLink) {
                    otherLink.classList.remove('active');
                }
            }
        });
        
        // Toggle current dropdown
        if (isOpen) {
            dropdown.classList.remove('show');
            link.classList.remove('active');
        } else {
            dropdown.classList.add('show');
            link.classList.add('active');
        }
        
        this.triggerEvent('dropdown-toggled', {
            link: link,
            dropdown: dropdown,
            isOpen: !isOpen
        });
    }
    
    handleResize() {
        const wasMobile = this.isMobile;
        this.checkMobile();
        
        if (wasMobile !== this.isMobile) {
            if (this.isMobile) {
                // Switching to mobile
                this.sidebar.classList.remove('collapsed');
                this.hide();
            } else {
                // Switching to desktop
                this.sidebar.classList.remove('show');
                this.sidebarOverlay.classList.remove('show');
                this.isVisible = false;
                document.body.style.overflow = '';
                
                // Restore collapsed state
                this.loadSavedState();
            }
        }
    }
    
    handleKeyboard(e) {
        // ESC key to close mobile sidebar
        if (e.key === 'Escape' && this.isMobile && this.isVisible) {
            this.hide();
        }
        
        // Alt + M to toggle sidebar
        if (e.altKey && e.key === 'm') {
            e.preventDefault();
            this.toggle();
        }
    }
    
    saveState() {
        if (!this.isMobile) {
            localStorage.setItem('sidebarCollapsed', this.isCollapsed);
        }
    }
    
    triggerEvent(eventName, data = {}) {
        const event = new CustomEvent(`sidebar:${eventName}`, {
            detail: data
        });
        document.dispatchEvent(event);
    }
    
    // Public API methods
    getState() {
        return {
            isCollapsed: this.isCollapsed,
            isMobile: this.isMobile,
            isVisible: this.isVisible
        };
    }
    
    openDropdown(menuKey) {
        const link = document.querySelector(`[data-menu="${menuKey}"]`);
        if (link && link.dataset.toggle === 'dropdown') {
            this.toggleDropdown(link);
        }
    }
    
    setActiveMenuItem(url) {
        // Remove existing active states
        document.querySelectorAll('.nav-link.active, .nav-dropdown-link.active').forEach(link => {
            link.classList.remove('active');
        });
        
        // Set new active state
        const targetLink = document.querySelector(`[href="${url}"]`);
        if (targetLink) {
            targetLink.classList.add('active');
            
            // If it's a dropdown item, expand its parent
            if (targetLink.classList.contains('nav-dropdown-link')) {
                const dropdown = targetLink.closest('.nav-dropdown');
                if (dropdown) {
                    dropdown.classList.add('show');
                    const parentLink = dropdown.previousElementSibling;
                    if (parentLink) {
                        parentLink.classList.add('active');
                    }
                }
            }
        }
    }
}

// Utility functions
const SidebarUtils = {
    // Search functionality
    search(query) {
        const links = document.querySelectorAll('.nav-link, .nav-dropdown-link');
        const results = [];
        
        links.forEach(link => {
            const text = link.textContent.toLowerCase();
            if (text.includes(query.toLowerCase())) {
                results.push({
                    element: link,
                    text: link.textContent.trim(),
                    url: link.getAttribute('href')
                });
            }
        });
        
        return results;
    },
    
    // Highlight search results
    highlightSearch(query) {
        const links = document.querySelectorAll('.nav-link, .nav-dropdown-link');
        
        links.forEach(link => {
            const textEl = link.querySelector('.nav-text, .nav-dropdown-text');
            if (textEl) {
                const originalText = textEl.textContent;
                const regex = new RegExp(`(${query})`, 'gi');
                
                if (query && originalText.toLowerCase().includes(query.toLowerCase())) {
                    textEl.innerHTML = originalText.replace(regex, '<mark>$1</mark>');
                } else {
                    textEl.textContent = originalText;
                }
            }
        });
    },
    
    // Clear search highlights
    clearSearchHighlight() {
        const marks = document.querySelectorAll('.nav-text mark, .nav-dropdown-text mark');
        marks.forEach(mark => {
            mark.outerHTML = mark.textContent;
        });
    },
    
    // Get breadcrumb for current page
    getBreadcrumb() {
        const breadcrumb = [];
        const activeLink = document.querySelector('.nav-dropdown-link.active');
        
        if (activeLink) {
            const dropdown = activeLink.closest('.nav-dropdown');
            if (dropdown) {
                const parentLink = dropdown.previousElementSibling;
                if (parentLink) {
                    breadcrumb.push({
                        text: parentLink.querySelector('.nav-text').textContent,
                        url: parentLink.getAttribute('href')
                    });
                }
            }
            
            breadcrumb.push({
                text: activeLink.querySelector('.nav-dropdown-text').textContent,
                url: activeLink.getAttribute('href')
            });
        } else {
            const activeMainLink = document.querySelector('.nav-link.active');
            if (activeMainLink) {
                breadcrumb.push({
                    text: activeMainLink.querySelector('.nav-text').textContent,
                    url: activeMainLink.getAttribute('href')
                });
            }
        }
        
        return breadcrumb;
    }
};

// Initialize sidebar when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.sidebarManager = new SidebarManager();
    window.sidebarUtils = SidebarUtils;
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SidebarManager, SidebarUtils };
}