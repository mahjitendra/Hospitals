<?php

namespace App\Controllers\Inventory;

use App\Controllers\BaseController;
use App\Models\Inventory\Item as ItemModel;
use App\Libraries\Core\Session;

/**
 * Class InventoryController
 *
 * Handles management of inventory items.
 */
class InventoryController extends BaseController
{
    private ItemModel $itemModel;
    private Session $session;

    public function __construct()
    {
        $this->itemModel = new ItemModel();
        $this->session = new Session();
    }

    /**
     * Displays a list of all inventory items.
     */
    public function index()
    {
        $items = $this->itemModel->findAll();
        $this->render('inventory.item.index', [
            'title' => 'Inventory Items',
            'items' => $items
        ]);
    }

    /**
     * Shows the form to add a new inventory item.
     */
    public function showCreateForm()
    {
        $this->render('inventory.item.create', [
            'title' => 'Add New Inventory Item'
        ]);
    }

    /**
     * Processes the creation of a new inventory item.
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /inventory/items/create');
            exit;
        }

        $data = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'category' => trim($_POST['category']),
            'quantity' => (int)($_POST['quantity'] ?? 0),
            'reorder_level' => (int)($_POST['reorder_level'] ?? 0),
            'unit_price' => (float)($_POST['unit_price'] ?? 0.00),
            'location' => trim($_POST['location']),
        ];

        if (empty($data['name']) || empty($data['category'])) {
            $this->session->flash('error', 'Item Name and Category are required.');
            header('Location: /inventory/items/create');
            exit;
        }

        if ($this->itemModel->create($data)) {
            $this->session->flash('success', 'Inventory item created successfully.');
            header('Location: /inventory/items');
            exit;
        } else {
            $this->session->flash('error', 'Failed to create inventory item.');
            header('Location: /inventory/items/create');
            exit;
        }
    }
}
