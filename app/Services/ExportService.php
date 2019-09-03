<?php
namespace App\Services;

use App\Repositories\ExportRepositoryInterface;
use Carbon\Carbon;

class ExportService
{
    public function __construct(ExportRepositoryInterface $repoExport)
    {
        $this->repoExport = $repoExport;
    }

    /**
     * Create a file name data.
     *
     * @param $fileType
     * @return string
     */
    public function makeFilename($fileType)
    {
        $current = Carbon::now()->toDateTimeString();
        $cfg = config('common.export');

        if (in_array($fileType, array_keys($cfg['types']))) {
            return $fileName = sprintf($cfg['file_name'], $current) . '.' . $fileType;
        }
    }

    /**
     * Retrieve data according to each file type
     *
     * @param $fileType
     * @param $request
     * @return array
     */
    public function getData($fileType, $request)
    {
        $cfg = config('common.export');
        switch ($fileType) {
            case $cfg['types']['csv']['value']:
                // phpcs:ignore Squiz.NamingConventions.ValidVariableName
                return $this->csv($cfg, $request->export_column, $request->separation, $request->encoding);
            case $cfg['types']['xlsx']['value']:
                return $this->excel();
        }
    }

    /**
     * Convert data to csv file
     *
     * @param $cfg
     * @param $exportColumn
     * @param $separation
     * @param $encoding
     * @return array
     */
    public function csv($cfg, $exportColumn, $separation, $encoding)
    {
        // Compare column request and config
        $exportColumns = array_intersect($exportColumn, array_keys($cfg['export_column']));
        $data = $this->repoExport->select($exportColumns);

        // Create a title for each column
        array_unshift($data, $exportColumns);

        // Compare delimiter request  and config
        $delimiter = $separation;

        // Converts characters so that the function reads the required characters correctly
        if ($delimiter === "\\t") {
            $delimiter = "\t";
        }

        $result = '';

        foreach ($data as $row) {
            $result .= implode($delimiter, $row) . "\r\n";
        }

        $dataConvert = mb_convert_encoding($result, config('common.export.encoding.' . $encoding), $cfg['encoding']['utf8']);

        return $data = [
            'raw' => $result,
            'encoded' => $dataConvert,
        ];
    }

    /**
     * Convert data to excel file
     *
     * @return array
     */
    public function excel()
    {
        $result = '';
        return $data = [
            'raw' => $result,
            'encoded' => $result,
        ];
    }
}
