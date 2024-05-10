<?php
/*
 -------------------------------------------------------------------------
 DLTeams plugin for GLPI
 -------------------------------------------------------------------------
 LICENSE : This file is part of DLTeams Plugin.

 DLTeams Plugin is a GNU Free Copylefted software.
 It disallow others people than DLPlace developers to distribute, sell,
 or add additional requirements to this software.
 Though, a limited set of safe added requirements can be allowed, but
 for private or internal usage only ;  without even the implied warranty
 of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

 You should have received a copy of the GNU General Public License
 along with DLTeams Plugin. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
  @package   dlteams
  @author    DLPlace developers
  @copyright Copyright (c) 2022 DLPlace
  @inspired	 DPO register plugin (Karhel Tmarr) & gdprropa (Yild)
  @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
  @link      https://github.com/dlplace/dlteams
  @since     2021
 --------------------------------------------------------------------------
 */

if (!defined('K_PATH_IMAGES')) {
    define('K_PATH_IMAGES', GLPI_ROOT . '/plugins/dlteams/images/');
}

/**
 * Class PluginDlteamsCreatePDFBase
 * Used partially as a wrapper around TCPDF class in order to simplify pdf generation
 */
class PluginDlteamsCreatePDFBase extends CommonGLPI
{

    protected TCPDF $pdf;

    protected $system_config;
    protected $print_options;

    public static function getTypeName($nb = 0)
    {

        return __("Create document", 'dlteams');
    }


    public function showPDF($generator_options, $filename = null)
    {


        if (!$filename)
            $filename = $this->generateFilename($generator_options) . ".pdf";
        ob_end_clean();

        $this->pdf->Output($filename);
    }


    /**
     * @param $generator_options
     * @return string
     */
    public function generateFilename($generator_options): string
    {
        $filename = "";

        $entity = new Entity();
        $entity->getFromDB(Session::getActiveEntity());

        $filename .= $entity->fields['name'];
        $filename .= "-";

        switch ($generator_options['report_type']) {
            case PluginDlteamsCreatePDF::REPORT_SINGLE_RECORD:
                $record = new PluginDlteamsRecord();
                $record->getFromDB($generator_options['record_id']);
                $filename .= $record->fields['number'];
                break;
            case PluginDlteamsCreatePDF::REPORT_ALL:
                $filename .= __("Complete report", 'dlteams');
                break;
            case PluginDlteamsCreatePDF::REPORT_BROADCAST_THRIDPARTIES:
                $filename .= __("Third party data protection politics", 'dlteams');
                break;
            case PluginDlteamsCreatePDF::REPORT_BROADCAST_EMPLOYEES:
                $filename .= __("Employees data protection politics", 'dlteams');
                break;
//            case PluginDlteamsCreatePDF::REPORT_BROADCAST_INTERNAL:
//                $filename .= __("Internal only", 'dlteams');
//                break;
            case PluginDlteamsCreatePDF::REPORT_BROADCAST_DELIVERABLE:
                $filename .= __("Deliverable", 'dlteams');
                break;
        }
        $filename = PluginDlteamsUtils::normalize($filename);
        return $filename;
    }

    protected function setHeader($title, $content, $logo_path)
    {

        $this->pdf->resetHeaderTemplate();

        if ($this->system_config['print']['logo_show']) {
            $this->pdf->SetHeaderData($logo_path, 15, $title, $content);
        } else {
            $this->pdf->SetHeaderData(null, 0, $title, $content);
        }

        $this->pdf->SetTitle($title);
        $this->pdf->SetY($this->pdf->GetY() + $this->pdf->getLastH());

    }

    protected function printPageTitle($html)
    {

        if ($this->HTML) echo $html;

        else $this->writeInternal(
            $html, [
            'fillcolor' => [130, 230, 180],
            'fill' => 1,
            'textcolor' => [0, 0, 0],
            'align' => 'C'
        ]);
    }

    protected function insertNewPageIfBottomSpaceLeft($bottom_space = 20)
    {

        if ($this->HTML) return;

        $pd = $this->pdf->getPageDimensions();
        if ($this->pdf->getY() + $this->pdf->getFooterMargin() + $bottom_space > $pd['hk']) {
            $this->pdf->addPage($this->print_options['page_orientation'], 'A4');
        }
    }

    protected function writeHtml($html, $params = [], $end_line = true)
    {

        if ($this->HTML) {
            echo $html;
            return;
        }

        $options = [
            'fillcolor' => [255, 255, 255],
            'textcolor' => [0, 0, 0],
            'linebefore' => 0,
            'lineafter' => 0,
            'ln' => true,
            'fill' => false,
            'reseth' => false,
            'align' => 'L',
            'autopadding' => true
        ];

        foreach ($params as $key => $value) {
            $options[$key] = $value;
        }

        $this->pdf->SetFillColor($options['fillcolor'][0], $options['fillcolor'][1], $options['fillcolor'][2]);
        $this->pdf->SetTextColor($options['textcolor'][0], $options['textcolor'][1], $options['textcolor'][2]);

        if ($options['linebefore'] > 0) {
            $this->pdf->Ln($options['linebefore']);
        }

        $this->pdf->writeHTML($html, $options['ln'], $options['fill'], $options['reseth'], $options['autopadding'], $options['align']);

        if ($end_line) {
            if ($options['lineafter'] > 0) {
                $this->pdf->Ln($options['lineafter']);
            }
            $this->pdf->SetY($this->pdf->GetY() + $this->pdf->getLastH());
        }
    }

    protected function writeInternal($html, $params = [], $end_line = true)
    {

        if ($this->HTML) {
            echo $html;
            return;
        }

        $options = [
            'fillcolor' => [255, 255, 255],
            'textcolor' => [0, 0, 0],
            'cellpading' => 1,
            'linebefore' => 0,
            'lineafter' => 0,
            'cellwidth' => 0,
            'cellheight' => 1,
            'xoffset' => '',
            'yoffset' => '',
            'border' => 0,
            'ln' => 0,
            'fill' => false,
            'reseth' => true,
            'align' => 'L',
            'autopadding' => true
        ];

        foreach ($params as $key => $value) {
            $options[$key] = $value;
        }

        $this->pdf->SetFillColor($options['fillcolor'][0], $options['fillcolor'][1], $options['fillcolor'][2]);
        $this->pdf->SetTextColor($options['textcolor'][0], $options['textcolor'][1], $options['textcolor'][2]);
        $this->pdf->SetCellPadding($options['cellpading']);

        if ($options['linebefore'] > 0) {
            $this->pdf->Ln($options['linebefore']);
        }

        $this->pdf->writeHTMLCell(
            $options['cellwidth'],
            $options['cellheight'],
            $options['xoffset'],
            $options['yoffset'],
            $html,
            $options['border'],
            $options['ln'],
            $options['fill'],
            $options['reseth'],
            $options['align'],
            $options['autopadding']
        );

        if ($end_line) {
            if ($options['lineafter'] > 0) {
                $this->pdf->Ln($options['lineafter']);
            }
            $this->pdf->SetY($this->pdf->GetY() + $this->pdf->getLastH());
        }
    }

    protected function write2ColsRow($col1_html = '', $col1_params = [], $col2_html = '', $col2_params = [])
    {

        if ($this->HTML) echo "<tr><td>" . $col1_html . "</td><td>" . $col2_html . "</td></tr>";

        else {
            $height = 0;

            $this->pdf->startTransaction();
            $this->writeInternal($col1_html, $col1_params, false);

            $height = ($height < $this->pdf->getLastH() ? $this->pdf->getLastH() : $height);

            $this->writeInternal($col2_html, $col2_params);

            $height = ($height < $this->pdf->getLastH() ? $this->pdf->getLastH() : $height);

            $this->pdf = $this->pdf->rollbackTransaction();
            $col1_params['cellheight'] = $height;
            $col2_params['cellheight'] = $height;

            $top_margin = 82;
            if ($this->pdf->getY() > (300 /* A4 height */ - $top_margin + 25 /* another magic constant */)) {
                $this->pdf->addPage();
            }

            $this->writeInternal($col1_html, $col1_params, false);
            $this->writeInternal($col2_html, $col2_params);
        }
    }

    function array_orderby()
    {

        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = [];
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field];
                }
                $args[$n] = $tmp;
            }
        }

        $args[] = &$data;
        call_user_func_array('array_multisort', $args);

        return array_pop($args);
    }

    function preparePrintOptions($print_options = [])
    {

        $this->system_config = PluginDlteamsConfig::getConfig();
        $this->print_options = $print_options;

    }

    function preparePDF()
    {

        $this->pdf = new PDF($this->print_options['page_orientation'], 'mm', 'A4', true, $this->system_config['print']['codepage'], false);

        $this->pdf->setHeaderFont([$this->system_config['print']['font_name'], 'B', 8]);
        $this->pdf->setFooterFont([$this->system_config['print']['font_name'], 'B', 8]);

        $this->pdf->SetMargins(
            $this->system_config['print']['margin_left'],
            $this->system_config['print']['margin_top'],
            $this->system_config['print']['margin_right'],
            true
        );

        $this->pdf->SetAutoPageBreak(true, $this->system_config['print']['margin_footer']);

        $this->pdf->SetFont($this->system_config['print']['font_name'], '', $this->system_config['print']['font_size']);

        $this->pdf->setHeaderMargin($this->system_config['print']['margin_header']);
        $this->pdf->setFooterMargin($this->system_config['print']['margin_footer']);
    }

    static function isGdprownerPluginActive()
    {

        $plugin = new Plugin();

        return $plugin->isActivated('gdprowner');
    }

}

// Extends TCPDF in order to override footer method, so it contains "dlplace.eu © YEAR"
class PDF extends TCPDF
{
    public function Footer()
    {

        $this->SetY(-15);
        // Set content : date & copyright
        $this->Cell(0, 5, "www.dlteams.fr © " . date("Y"), 0, false, 'L', 0);

        // From base method :
        // Get page number
        $w_page = isset($this->l['w_page']) ? $this->l['w_page'] . ' ' : '';
        if (empty($this->pagegroups)) {
            $pagenumtxt = $w_page . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages();
        } else {
            $pagenumtxt = $w_page . $this->getPageNumGroupAlias() . ' / ' . $this->getPageGroupAlias();
        }
        //Print page number
        if ($this->getRTL()) {
            $this->SetX($this->original_rMargin);
            $this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
        } else {
            $this->SetX($this->original_lMargin);
            $this->Cell(0, 0, $this->getAliasRightShift() . $pagenumtxt, 'T', 0, 'R');
        }
    }
}
