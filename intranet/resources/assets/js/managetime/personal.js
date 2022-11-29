$(function () {
    // Fix side bar always display
    var $sidebarContainer = $('.timekeeping-personal-sidebar'),
        $sidebar = $sidebarContainer.children(".timekeeping-note-sidebar"),
        $detailSidebar = $sidebarContainer.children(".timekeeping-detail-sidebar"),
        $contentContainer = $sidebarContainer.parent().siblings(),
        $window = $(window),
        $document = $(document);
    var sidebarTop = $sidebarContainer.offset().top + 20,
        sidebarHeight = $sidebar.height(),
        docHeight = $document.height(),
        windowWidth = $window.width(),
        windowHeight = $window.height(),
        sidebarContainerHeight = $sidebarContainer.height(),
        contentContainerHeight = $contentContainer.height(),
        detailSidebarHeight = $detailSidebar.height(),
        topPadding = 30;

    // debounce delay 100ms event scroll
    var handleScroll = debounce(function () {
        if (windowWidth > 992) {
            var scrollTop = $window.scrollTop(),
                marginTop = parseInt($sidebar.css('margin-top'));
            // not fix scroll sidebar if height sidebar container >= height content and margin-top of sidebar = 0
            if (sidebarContainerHeight > contentContainerHeight && marginTop === 0) {
                return;
            }
            // scroll to bottom page => update scrollTop
            scrollTop = Math.min(scrollTop, docHeight - windowHeight);
            // max margin-top is offset bottom of sidebar = offset bottom of content container
            marginTop = Math.min(
                scrollTop - sidebarTop + topPadding,
                contentContainerHeight - detailSidebarHeight - sidebarHeight - 20
            );

            if (scrollTop > sidebarTop) {
                $sidebar.stop().animate({
                    marginTop: marginTop
                });
            } else {
                $sidebar.stop().animate({
                    marginTop: 0
                });
            }
        } else {
            // scroll-X window to tablet or mobile (width <= 992px)
            $sidebar.css('margin-top', 0);
        }
    }, 100);
    $window.scroll(handleScroll);

    $window.resize(debounce(function () {
        docHeight = $document.height();
        windowWidth = $window.width();
        windowHeight = $window.height();
        sidebarContainerHeight = $sidebarContainer.height();
        contentContainerHeight = $contentContainer.height();
        detailSidebarHeight = $detailSidebar.height();
        sidebarTop = $sidebarContainer.offset().top + detailSidebarHeight + 20;
        sidebarHeight = $sidebar.height();
    }, 50));
});
