<?php

namespace App\Controllers\Inventory;

use App\Controllers\BaseController;
use App\Models\Inventory\Vendor as VendorModel;
use App\Libraries\Core\Session;

/**
 * Class VendorController
 *
 * Handles management of vendors.
 */
class VendorController extends BaseController
{
    private VendorModel $vendorModel;
    private Session $session;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
        $this->session = new Session();
    }

    /**
     * Displays a list of all vendors.
     */
    public function index()
    {
        $vendors = $this->vendorModel->findAll();
        $this->render('inventory.vendor.index', [
            'title' => 'Vendors',
            'vendors' => $vendors
        ]);
    }

    /**
     * Shows the form to add a new vendor.
     */
    public function showCreateForm()
    {
        $this->render('inventory.vendor.create', [
            'title' => 'Add New Vendor'
        ]);
    }

    /**
     * Processes the creation of a new vendor.
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /inventory/vendors/create');
            exit;
        }

        $data = [
            'name' => trim($_POST['name']),
            'contact_person' => trim($_POST['contact_person']),
            'phone_number' => trim($_POST['phone_number']),
            'email' => trim($_POST['email']),
            'address' => trim($_POST['address']),
        ];

        if (empty($data['name'])) {
            $this->session->flash('error', 'Vendor Name is required.');
            header('Location: /inventory/vendors/create');
            exit;
        }

        if ($this->vendorModel->create($data)) {
            $this->session->flash('success', 'Vendor created successfully.');
            header('Location: /inventory/vendors');
            exit;
        } else {
            $this->session->flash('error', 'Failed to create vendor.');
            header('Location: /inventory/vendors/create');
            exit;
        }
    }
}
