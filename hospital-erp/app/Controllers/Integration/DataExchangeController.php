<?php

namespace App\Controllers\Integration;

use App\Controllers\BaseController;
use App\Models\Integration\DataMapping as DataMappingModel;

/**
 * Class DataExchangeController
 *
 * Handles the management of data mappings for integrations.
 */
class DataExchangeController extends BaseController
{
    private DataMappingModel $dataMappingModel;

    public function __construct()
    {
        $this->dataMappingModel = new DataMappingModel();
    }

    /**
     * Displays a list of all data mappings.
     */
    public function index()
    {
        $mappings = $this->dataMappingModel->findAll();
        $this->render('integration.datamapping.index', [
            'title' => 'Data Mappings',
            'mappings' => $mappings
        ]);
    }
}
