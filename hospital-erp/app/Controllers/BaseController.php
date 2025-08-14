<?php

namespace App\Controllers;

/**
 * Class BaseController
 *
 * This controller should be extended by all other controllers.
 * It provides a convenient place for loading components and performing
 * functions that are needed by all your controllers.
 */
abstract class BaseController
{
    /**
     * Renders a view file with data.
     *
     * This method loads a view file and a layout, passing data to them.
     * The view content is injected into the layout.
     *
     * @param string $view The view file to render (e.g., 'dashboard.index').
     * @param array  $data An associative array of data to be extracted for the view.
     * @throws \Exception If the view file is not found.
     */
    protected function render(string $view, array $data = [], string $layout = 'main')
    {
        // Construct the full path to the view file
        // The dot notation 'folder.file' is converted to 'folder/file.php'
        $viewPath = ROOT_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$viewPath}");
        }

        // Make the data array keys available as variables in the view
        extract($data);

        // Start output buffering to capture the view's output
        ob_start();

        // Include the view file, which will be processed and its output captured
        require $viewPath;

        // Get the captured output and end buffering
        $content = ob_get_clean();

        // Now, require the specified layout file. The layout file will have access to
        // the $content variable and any variables from the $data array.
        $layoutPath = ROOT_PATH . '/app/Views/layouts/' . $layout . '.php';

        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout file not found: {$layoutPath}");
        }

        require $layoutPath;
    }
}
