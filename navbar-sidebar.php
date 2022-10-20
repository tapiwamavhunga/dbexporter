    <nav id="navbar" class="navbar navbar-default navbar-fixed-top">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#sidebar" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">
                <img src="images/csv-import-export.png" alt="" />
            </a>
        </div>
    </nav>

    <nav id="sidebar" class="navbar navbar-inverse navbar-fixed-top with-navbar collapse">
        <ul class="nav navbar-nav">
            <li<?php if ($current_page == 'import-list' or $current_page == 'import-log' or $current_page == 'import') echo ' class="active"'; ?>>
                <a href="import-list.php">
                    <i class="icon wb-download"></i>
                    Import
                </a>
                <ul<?php if ($current_page != 'import-list' and $current_page != 'import-log' and $current_page != 'import') echo ' style="display: none;"'; ?>>
                    <li<?php if ($current_page == 'import') echo ' class="active"'; ?>><a href="import.php">New</a></li>
                    <li<?php if ($current_page == 'import-list' or $current_page == 'import-log') echo ' class="active"'; ?>><a href="import-list.php">List</a></li>
                </ul>
            </li>
            <li<?php if ($current_page == 'export-list' or $current_page == 'export-log' or $current_page == 'export') echo ' class="active"'; ?>>
                <a href="export-list.php">
                    <i class="icon wb-upload"></i>
                    Export
                </a>
                <ul<?php if ($current_page != 'export-list' and $current_page != 'export-log' and $current_page != 'export') echo ' style="display: none;"'; ?>>
                    <li<?php if($current_page == 'export') echo ' class="active"'; ?>><a href="export.php">New</a></li>
                    <li<?php if($current_page == 'export-list' or $current_page == 'export-log') echo ' class="active"'; ?>><a href="export-list.php">List</a></li>
                </ul>
            </li>
            <li<?php if ($current_page == 'settings') echo ' class="active"'; ?>>
                <a href="settings.php">
                    <i class="icon wb-settings"></i>
                    Settings
                </a>
            </li>
        </ul>
    </nav>