<?php
 /**     
 * The technical support is guaranteed for all modules proposed by Wyomind.
 * The below code is obfuscated in order to protect the module's copyright as well as the integrity of the license and of the source code.
 * The support cannot apply if modifications have been made to the original source code (https://www.wyomind.com/terms-and-conditions.html).
 * Nonetheless, Wyomind remains available to answer any question you might have and find the solutions adapted to your needs.
 * Feel free to contact our technical team from your Wyomind account in My account > My tickets. 
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassProductImport\Model\ResourceModel\Type;  class System extends \Wyomind\MassStockUpdate\Model\ResourceModel\Type\System {public $x56=null;public $x67=null;public $x00=null;  public $fields;  public $attributeSet = [];  public $website = [];  private $x53b = null;  private $x550 = null;  private $x561 = null;  protected $_helperData = null;  public $removeQueries = [];  public $error = "Ma\x73s\40\120\x72\x6fd\x75\143\x74 \x49\155\x70\x6frt\x20&\x20\x55\x70\x64\x61\164\145 -\40\40\111n\166\x61\x6c\151\x64 \114i\x63\145nse\x21";  const WEBSITE_SEPARATOR = "\54";  public function __construct( \Magento\Framework\Model\ResourceModel\Db\Context $context, \Wyomind\Core\Helper\Data $coreHelper, \Wyomind\MassProductImport\Helper\Data $helperData, \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection, \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory, \Magento\Framework\Stdlib\DateTime\DateTime $coreDate, \Magento\Store\Model\WebsiteRepository $websiteRepository, $connectionName = null ) { $coreHelper->constructor($this, func_get_args()); $this->{$this->x67->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->x543}}} = $attributeSetCollectionFactory; $this->{$this->x56->x4ca->{$this->x56->x4ca->x56c}} = $helperData; $this->{$this->x67->x4ca->{$this->x00->x4ca->x554}} = $websiteRepository; $this->{$this->x56->x4f9->xd0f} = $coreDate; $this->_coreHelper = $coreHelper; parent::__construct($context, $coreHelper, $helperData, $entityAttributeCollection, $connectionName); }  public function _construct() {$x104 = $this->x00->x4f9->{$this->x67->x4f9->x1046};$xaf = $this->x00->x4f9->{$this->x00->x4f9->x104f};$xf6 = $this->x00->x4f9->{$this->x00->x4f9->{$this->x67->x4f9->{$this->x00->x4f9->x105f}}}; $this->table = $this->{$this->x67->x4ca->x9e7}("\143a\x74\141lo\x67\137\160\162\157\144uc\164\137\145n\x74\151\164\x79"); $this->tableSequence = $this->{$this->x67->x4ca->x9e7}("\163e\161u\x65\x6e\x63e_\160\162\x6f\x64\x75c\x74"); $this->tableCpei = $this->{$this->x67->x4ca->x9e7}("c\x61\164\x61l\157g\137\x70\162\x6f\144uc\164\137e\x6e\x74\x69\164\x79_\151n\x74"); $this->tableCpw = $this->{$this->x67->x4ca->x9e7}("\143a\164\x61\x6c\x6f\147_\160\162\157\144\x75ct\137\x77e\x62s\x69\x74\x65"); $this->tableCsi = $this->{$this->x67->x4ca->x9e7}("\x63\x61\164\141lo\147\x69\x6e\166\145\156\164o\x72\171\x5fstoc\153\137\151t\145\155"); $this->tableEa = $this->{$this->x67->x4ca->x9e7}("\145\141v\137at\x74\162\151bu\164\x65"); ${$this->x00->x4f9->xd87} = $this->{$this->x67->x4ca->xa36}(); ${$this->x00->x4ca->{$this->x00->x4ca->x60c}} = $this->{$this->x67->x4ca->x9e7}('eav_entity_type'); ${$this->x00->x509->{$this->x67->x509->{$this->x00->x509->{$this->x67->x509->x14fa}}}} = ${$this->x67->x4ca->x5ff}->{$this->x67->x4ca->xa48}()->{$this->x56->x4ca->xa4f}(${$this->x67->x509->{$this->x67->x509->{$this->x00->x509->{$this->x56->x509->{$this->x00->x509->x14ec}}}}})->{$this->x67->x4ca->xa5a}('entity_type_code=\'catalog_product\''); ${$this->x67->x4ca->{$this->x56->x4ca->x626}} = ${$this->x00->x4f9->{$this->x56->x4f9->xd88}}->{$this->x56->x4ca->xa65}(${$this->x56->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->x61e}}}}); ${$this->x67->x509->{$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x00->x509->x1514}}}}} = ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->{$this->x56->x4ca->x62b}}}}[0]['entity_type_id']; ${$this->x56->x509->{$this->x00->x509->{$this->x56->x509->{$this->x56->x509->x1520}}}} = $this; ${$this->x56->x509->{$this->x56->x509->{$this->x56->x509->{$this->x67->x509->x152b}}}} = $x104($xaf()); $this->${$this->x56->x509->{$this->x56->x509->{$this->x56->x509->{$this->x67->x509->x152b}}}} = ""; ${$this->x56->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->xdc2}}} = "\x65\162ro\x72"; ${$this->x56->x4f9->xdaf}->_coreHelper->{$this->x56->x4ca->x9c0}(${$this->x56->x4f9->{$this->x00->x4f9->xdb2}}, ${$this->x56->x509->{$this->x56->x509->{$this->x00->x509->x1526}}});  ${$this->x56->x4f9->xdc9} = $this->{$this->x67->x4ca->{$this->x00->x4ca->x53f}}->{$this->x56->x4ca->xa7e}()->{$this->x56->x4ca->xa89}(${$this->x67->x509->{$this->x56->x509->{$this->x56->x509->{$this->x67->x509->x150f}}}}); ${$this->x67->x509->x1539}->{$this->x00->x4ca->xa9b}("ent\151\x74\x79\137\x74\x79\160\x65_i\x64", ["\x65q" => ${$this->x67->x509->{$this->x56->x509->{$this->x56->x509->{$this->x67->x509->x150f}}}}]); ${$this->x00->x4ca->x658} = ${$this->x56->x4f9->xdc9}->{$this->x00->x4ca->xaac}(); foreach (${$this->x00->x4ca->x658} as ${$this->x56->x4f9->{$this->x67->x4f9->{$this->x67->x4f9->{$this->x56->x4f9->xde5}}}} => ${$this->x56->x4f9->xdea}) { ${$this->x00->x509->{$this->x56->x509->{$this->x56->x509->{$this->x00->x509->{$this->x00->x509->x156c}}}}} = ${$this->x56->x509->{$this->x00->x509->{$this->x67->x509->x1559}}}["e\156\164\x69t\171\x5ft\x79p\x65\137\x69d"]; ${$this->x00->x4f9->xe00} = ${$this->x56->x4f9->xdea}["\141\164\x74\x72\151\x62\x75\164\145\137\163\x65t\137na\x6d\145"]; $this->{$this->x67->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->x52e}}}}[${$this->x56->x509->{$this->x00->x509->{$this->x67->x509->{$this->x56->x509->x155d}}}}["\x61t\164\x72ibut\145\x5f\x73et\x5f\x69\144"]] = $xf6(${$this->x00->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xe06}}}); } foreach ($this->{$this->x67->x4ca->{$this->x00->x4ca->x554}}->{$this->x56->x4ca->xac2}() as ${$this->x56->x509->{$this->x56->x509->x157c}}) { $this->websites[${$this->x56->x4ca->x68d}->{$this->x00->x4ca->xad1}()] = $xf6(${$this->x56->x509->{$this->x56->x509->x157c}}->{$this->x00->x4ca->xad8}()); } if (${$this->x56->x4f9->xdaf}->${$this->x56->x4ca->{$this->x00->x4ca->x64b}} != $x104(${$this->x56->x509->{$this->x56->x509->{$this->x00->x509->x1526}}})) { throw new \Magento\Framework\Exception\LocalizedException(__(${$this->x67->x4ca->x638}->${$this->x67->x4ca->x64d})); } parent::{$this->x56->x4ca->xae3}(); }  public function beforeCollect($x189, $x18b) {$x17a = $this->x00->x4f9->x1066;  ${$this->x67->x4ca->x6a8} = ${$this->x00->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->xe1d}}}}->{$this->x56->x4ca->xaf6}(); ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->{$this->x56->x4ca->x6bb}}}} = ${$this->x00->x509->{$this->x56->x509->{$this->x56->x509->{$this->x00->x509->x1587}}}}->{$this->x67->x4ca->xb02}(); if (${$this->x67->x509->x1596}) { switch (${$this->x67->x4f9->{$this->x67->x4f9->xe2b}}) {  case 1: if (${$this->x00->x4f9->xe2c} == 0) {  ${$this->x56->x509->{$this->x56->x509->x15b1}} = "\x55\x50\104\x41TE\x20" . $this->tableCpei . " \111\116NE\x52\x20J\x4fI\x4e\40" . $this->table . " \x4f\116\x20" . $this->table . "\x2een\x74\151\x74y\137\x69\x64\x20\75 " . $this->tableCpei . ".\145\156\x74\x69\x74\x79\137\151d\40\123ET\x20\x76\141\154\165\145\x3d\62" . "\40W\x48\105\122\105\40\140a\x74\x74\162i\142\165\x74\145_\151\144\140\x3d\50\123\x45\114E\103T\x20a\164\x74ri\142\165\x74e\x5f\151\x64\40\106\122\x4f\115\x20" . $this->tableEa . "\x20\x57\110\105\x52E\x20\x61\164\x74r\x69\142\165t\145\137c\x6fde=\x27\163\x74\141t\165s\47)" . " \101\116\104\40" . $this->tableCpei . "\x2eenti\164\x79\x5f\x69d=%\163 \101\116D\40\x28" . $this->table . ".\x63\x72\145\x61\164\145\x64_by\40\x3d\40" . ${$this->x56->x4ca->{$this->x56->x4ca->x69b}}->{$this->x00->x4ca->xad1}() . " \117R " . $this->table . ".\x75p\144a\x74\145\x64_\x62y \x3d\x20" . ${$this->x00->x509->{$this->x56->x509->{$this->x56->x509->{$this->x00->x509->x1587}}}}->{$this->x00->x4ca->xad1}() . ")\x3b"; } elseif (${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->{$this->x56->x4ca->x6bb}}}} == 1) {  ${$this->x67->x4ca->x6be} = "\x55\x50D\x41TE\40" . $this->tableCpei . " INNE\122 \112\x4f\111N\40" . $this->table . " \117N " . $this->table . "\56en\x74i\x74\171_\151d\40\75\40" . $this->tableCpei . "\56\x65\156\164i\x74\x79\137i\144 \x53\x45\124\x20v\x61l\x75\x65=\62" . "\40WH\x45R\105\x20\x60a\164tr\x69\x62u\x74\145\137id\x60\x3d\x28S\105L\x45\x43\x54\x20a\164\x74r\151b\x75\164\x65\x5f\x69\x64 \x46\122O\115 " . $this->tableEa . "\x20\x57\110\105\122E\x20a\164\x74\x72i\142\x75\x74e_c\157\x64\145=\x27\163t\x61t\x75s\x27)" . "A\116\104\40" . $this->tableCpei . "\x2e\x65ntit\x79\137\151\144=\x25\163\40A\116D " . $this->table . ".\143\162e\x61t\145\x64_\142y\40!= " . ${$this->x00->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->xe1b}}}->{$this->x00->x4ca->xad1}() . " A\116D\x20" . $this->table . ".\x75\x70\x64\141\164\x65\x64\137by \x21\75\x20" . ${$this->x56->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->x69c}}}->{$this->x00->x4ca->xad1}() . "\x3b"; } else {  ${$this->x56->x4f9->{$this->x00->x4f9->{$this->x56->x4f9->xe34}}} = "\x55\120\x44\101\x54\x45\40 `" . $this->tableCpei . "\x60\x20S\105\x54 \166\141\154\x75\145\75\62\40\x57\110\105\122\x45 `\x61\x74t\162\151\142u\164e\137\x69d`=\x28\x53E\114\105\103\124 \x61\x74t\162i\142\165\164\x65\137id\x20\x46\x52O\115 " . $this->tableEa . "\40W\110\x45R\105\x20a\x74t\x72i\142\x75t\145_co\x64\x65\x3d'stat\x75s\47)\x20\101\116\104\40\x65n\x74\x69\164\x79\x5f\x69\144\x3d\45\x73;"; } break;  case 2: if (${$this->x00->x4f9->{$this->x67->x4f9->xe2d}} == 0) {  ${$this->x56->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->xe39}}}} = "D\105\114E\124\105 \106\x52\117\115\40" . $this->table . " W\x48\105\x52E\40\140\145n\164\151\164\171\x5fi\x64`\x3d\x25\163 AN\x44\40\50\140crea\164\x65\x64_\x62y\x60\40\75\40" . ${$this->x56->x4ca->{$this->x56->x4ca->x69b}}->{$this->x00->x4ca->xad1}() . "\x20\x4f\122 \140\165p\144a\164\145\144_\x62y\x60 =\x20" . ${$this->x00->x509->{$this->x67->x509->x1580}}->{$this->x00->x4ca->xad1}() . "\x29\x3b"; } elseif (${$this->x56->x509->{$this->x56->x509->x15aa}} == 1) {  ${$this->x67->x4ca->x6be} = "\x44E\x4cE\x54E \x46R\117M\x20" . $this->table . " WH\105\122\x45\x20\140\x65\x6e\164\x69ty_\x69\x64`\x3d\x25\163\x20\x41ND\x20\x60c\162\x65a\x74\x65d_\142y`\x20\x21\x3d\40" . ${$this->x56->x4f9->xe15}->{$this->x00->x4ca->xad1}() . " \101\x4e\x44\40\x60\x75pd\141te\144_b\x79\140 \x21\75\x20" . ${$this->x00->x509->{$this->x56->x509->{$this->x56->x509->{$this->x00->x509->x1587}}}}->{$this->x00->x4ca->xad1}() . "\73"; } else {  ${$this->x56->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xe3a}}}}} = "D\105LE\124\x45\x20\106R\117\115\x20" . $this->table . " WH\105\x52\105\x20\140\145\x6eti\x74\x79\137\x69\x64`=%\163"; } break;  case 3: if (${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->{$this->x56->x4ca->x6bb}}}} == 0) {  ${$this->x56->x509->{$this->x56->x509->x15b1}} = "UP\x44\101\124E " . $this->tableCsi . "\40\111\116\x4e\x45R \112\x4f\111\116\x20" . $this->table . "\x20\117\x4e " . $this->table . "\56\x65\156\x74\x69\x74y_i\144\x20\x3d " . $this->tableCsi . ".\x70\x72\157\144\165\143\x74\137i\144 \123E\x54 \x69s\137\x69\156_\163\164\157\143\153=\x30" . "\40\127\110\105RE " . $this->tableCsi . "\56\x70\162o\144\165\x63\164_\x69\144=\x25\163\x20\101ND \x28" . $this->table . "\56\x63\162\x65a\x74\145\x64\x5f\x62\x79\40\75 " . ${$this->x56->x4ca->x696}->{$this->x00->x4ca->xad1}() . "\x20\x4f\122\40" . $this->table . "\x2e\165\x70\x64\141\164\x65d\x5f\x62\171\40\75 " . ${$this->x00->x509->{$this->x56->x509->{$this->x56->x509->{$this->x00->x509->x1587}}}}->{$this->x00->x4ca->xad1}() . "\51\73"; } elseif (${$this->x67->x4ca->{$this->x00->x4ca->{$this->x67->x4ca->x6b7}}} == 1) {  ${$this->x00->x4ca->{$this->x67->x4ca->x6c2}} = "\125\x50\104A\124E\40" . $this->tableCsi . " IN\x4e\x45R\40\x4aOI\116\x20" . $this->table . " O\116\40" . $this->table . "\x2ee\x6e\x74\151\164\171_\x69\144\x20=\x20" . $this->tableCsi . ".\x70\x72od\x75ct\137\x69d\40SE\124\40\x69s_\x69n_s\164\x6f\143\153=\x30" . "\40\127\x48\105R\x45\x20" . $this->tableCsi . ".\x70\x72\157d\x75\x63\164\x5f\x69d=\45s\40\101N\104\x20" . $this->table . "\x2e\143\162e\141\x74\x65d\137by !\x3d " . ${$this->x56->x4f9->xe15}->{$this->x00->x4ca->xad1}() . "\x20\x41\116\104\x20" . $this->table . ".\165\x70\x64\141\164\145\x64_b\x79\40\x21\x3d\x20" . ${$this->x00->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->xe1b}}}->{$this->x00->x4ca->xad1}() . "\x3b"; } else {  ${$this->x67->x509->x15ac} = "\x55\x50\104ATE " . $this->tableCsi . "\x20SET\40\x69\163\x5f\x69\156\137\163to\143\153\x3d\x30\x20W\x48\x45\122\x45 \160\162\157\x64\165\x63\x74\137\x69d\x3d\45s\73"; } break; } ${$this->x00->x509->{$this->x67->x509->{$this->x56->x509->x15be}}} = ${$this->x56->x4ca->x696}->_products; foreach (${$this->x67->x4ca->{$this->x56->x4ca->x6ce}} as ${$this->x56->x4ca->{$this->x00->x4ca->{$this->x67->x4ca->{$this->x00->x4ca->x6db}}}}) { $this->{$this->x56->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->x576}}}[${$this->x67->x4ca->x6d5}] = $x17a(${$this->x00->x4ca->{$this->x67->x4ca->x6c2}}, ${$this->x56->x4ca->{$this->x00->x4ca->x6d8}}); } } parent::{$this->x67->x4ca->xbb8}(${$this->x56->x509->x157e}, ${$this->x00->x4ca->{$this->x00->x4ca->{$this->x67->x4ca->x6a4}}}); }  public function updatequeries($x1a6, $x1aa) {$x19c = $this->x00->x4ca->{$this->x56->x4ca->{$this->x56->x4ca->x904}}; if ($x19c(${$this->x67->x509->{$this->x67->x509->x15d0}})) { unset($this->{$this->x56->x4ca->{$this->x67->x4ca->x574}}[${$this->x67->x4f9->xe50}]); } return parent::{$this->x00->x4ca->xbc1}(${$this->x00->x4ca->x6e4}, ${$this->x67->x4f9->{$this->x00->x4f9->{$this->x67->x4f9->xe5f}}}); }  public function reset() { $this->{$this->x00->x4ca->{$this->x67->x4ca->x516}} = []; }  public function collect($x26b, $x26e, $x273, $x278) {$x263 = $this->x00->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->{$this->x67->x4f9->x108d}}};$x1d1 = $this->x56->x4ca->x91b;$x222 = $this->x56->x4f9->{$this->x00->x4f9->{$this->x67->x4f9->{$this->x00->x4f9->x10b1}}};$x23d = $this->x00->x509->{$this->x00->x509->x1825};$x243 = $this->x67->x4f9->{$this->x67->x4f9->x10c9};$x22c = $this->x67->x509->{$this->x56->x509->{$this->x00->x509->x184a}}; list(${$this->x67->x509->{$this->x56->x509->x1614}}) = ${$this->x67->x509->{$this->x00->x509->x15fe}}['option']; ${$this->x00->x509->{$this->x00->x509->{$this->x67->x509->x161a}}} = $this; ${$this->x67->x509->{$this->x00->x509->{$this->x56->x509->x1630}}} = $x263($x1d1()); $this->${$this->x00->x4ca->{$this->x00->x4ca->x73a}} = ""; ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x67->x4ca->{$this->x00->x4ca->x74a}}}}} = "\145r\x72\157r"; ${$this->x56->x4f9->{$this->x00->x4f9->{$this->x67->x4f9->{$this->x67->x4f9->xea2}}}}->_coreHelper->{$this->x56->x4ca->x9c0}(${$this->x67->x4f9->xe99}, ${$this->x67->x509->{$this->x00->x509->{$this->x00->x509->{$this->x67->x509->x1634}}}}); switch (${$this->x67->x4f9->{$this->x00->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xe97}}}}) { case "\164\171p\145\x5f\x69d": $this->{$this->x00->x4ca->{$this->x67->x4ca->x516}}[${$this->x67->x4f9->{$this->x00->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xe97}}}}] = $x222(${$this->x00->x509->{$this->x67->x509->{$this->x67->x509->{$this->x67->x509->x15f5}}}}); break; case "at\164r\x69\x62\165te\x5f\x73\x65t_\151\144": ${$this->x00->x509->{$this->x56->x509->x15f1}} = $x222(${$this->x67->x4ca->x705}); if (isset($this->{$this->x67->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->x52e}}}}[${$this->x00->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->xe77}}}]) || $x23d(${$this->x00->x509->{$this->x56->x509->x15f1}}, $this->{$this->x67->x4ca->{$this->x56->x4ca->x527}})) { if ($x23d(${$this->x00->x4ca->{$this->x67->x4ca->x708}}, $this->{$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->x52a}}})) { ${$this->x00->x509->{$this->x67->x509->{$this->x67->x509->{$this->x67->x509->x15f5}}}} = $x243(${$this->x67->x4f9->xe70}, $this->{$this->x67->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->x52e}}}}); } } else { break; } $this->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->{$this->x67->x4ca->x51b}}}}[${$this->x67->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->xe92}}}] = ${$this->x00->x4ca->{$this->x67->x4ca->{$this->x67->x4ca->{$this->x56->x4ca->x70d}}}}; break; case "\167\145bs\x69\x74\x65": ${$this->x00->x509->{$this->x67->x509->{$this->x67->x509->x15f4}}} = $x222(${$this->x67->x4ca->x705}); ${$this->x56->x509->{$this->x00->x509->x1643}} = $x22c(self::WEBSITE_SEPARATOR, ${$this->x67->x4ca->x705}); $this->queries[$this->queryIndexer][] = "\x44\x45LET\105\40F\122\x4f\115 " . $this->tableCpw . "\x20W\110\105\122\x45\x20pr\157duc\x74_\151\x64=" . ${$this->x67->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->xe68}}}} . "\x3b"; foreach (${$this->x56->x509->{$this->x00->x509->x1643}} as ${$this->x56->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->xecb}}}) { if (isset($this->websites[${$this->x67->x4ca->x757}]) || $x23d(${$this->x56->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->xecb}}}, $this->websites)) { if ($x23d(${$this->x67->x4ca->{$this->x67->x4ca->x759}}, $this->websites)) { ${$this->x00->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->xe77}}} = $x243(${$this->x67->x4ca->{$this->x00->x4ca->{$this->x67->x4ca->x75c}}}, $this->websites); } else { ${$this->x00->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->xe77}}} = ${$this->x00->x509->x164d}; } $this->queries[$this->queryIndexer][] = "\111\x4eS\105R\124\x20\x49N\x54\x4f\40" . $this->tableCpw . "\x20(\x70\x72od\x75\143\164\137\x69d\54w\x65b\x73\151\164e_\151\x64)\x20\126\x41LU\105\x53\x28" . ${$this->x56->x4ca->{$this->x67->x4ca->{$this->x67->x4ca->{$this->x67->x4ca->x701}}}} . "\x2c" . ${$this->x67->x4f9->xe70} . "\x29\x3b"; } } break; default: $this->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x67->x4ca->x518}}}[${$this->x67->x4f9->{$this->x56->x4f9->xe8f}}] = ${$this->x67->x4f9->xe70}; } if (${$this->x00->x509->{$this->x00->x509->{$this->x67->x509->{$this->x00->x509->{$this->x56->x509->x1623}}}}}->${$this->x67->x509->{$this->x56->x509->x162d}} != $x263(${$this->x67->x4f9->xea4})) { throw new \Magento\Framework\Exception\LocalizedException(__(${$this->x67->x4f9->xe99}->${$this->x67->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x67->x4ca->{$this->x00->x4ca->x74a}}}}})); } parent::{$this->x00->x4ca->xbd9}(${$this->x67->x4f9->xe60}, ${$this->x00->x4ca->{$this->x67->x4ca->{$this->x00->x4ca->x70b}}}, ${$this->x56->x4ca->{$this->x56->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->x718}}}}, ${$this->x67->x4ca->x71a}); }  public function prepareQueries($x30f, $x310) {$x2ba = $this->x56->x509->{$this->x56->x509->x1856};$x28a = $this->x67->x4ca->{$this->x00->x4ca->x96d};$x2d2 = $this->x00->x4ca->x977;$x2f2 = $this->x00->x4f9->x1107;$x300 = $this->x00->x4f9->x1110;$x307 = $this->x67->x509->{$this->x56->x509->{$this->x56->x509->{$this->x56->x509->{$this->x00->x509->x189a}}}}; $this->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x67->x4ca->x518}}}["\x75\x70\x64a\164\145\x64_a\164"] = $this->{$this->x67->x4f9->{$this->x00->x4f9->xd14}}->date("Y-\155\x2dd \150\x3a\151:\163"); $this->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->{$this->x67->x4ca->x51b}}}}["u\x70da\164\145d\x5f\142\x79"] = ${$this->x56->x4f9->xee2}->{$this->x00->x4ca->xad1}(); if ($x28a(${$this->x67->x4ca->{$this->x56->x4ca->{$this->x56->x4ca->{$this->x56->x4ca->{$this->x00->x4ca->x76d}}}}})) { ${$this->x56->x509->{$this->x00->x509->{$this->x67->x509->x167c}}} = []; foreach ($this->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->{$this->x67->x4ca->x51b}}}} as ${$this->x00->x4f9->xefa} => ${$this->x67->x509->x1694}) { ${$this->x56->x4f9->{$this->x00->x4f9->{$this->x67->x4f9->xef5}}}[] = "`" . ${$this->x67->x4f9->{$this->x67->x4f9->{$this->x67->x4f9->{$this->x56->x4f9->xf01}}}} . "\x60\40\x3d\40\47" . ${$this->x67->x509->{$this->x56->x509->{$this->x67->x509->{$this->x00->x509->{$this->x00->x509->x16a1}}}}} . "'"; } if ($this->_coreHelper->{$this->x56->x4ca->xbee}("\115\141g\x65\x6et\x6f\x5fEn\164erpr\151\x73\145")) { ${$this->x00->x4ca->{$this->x67->x4ca->{$this->x56->x4ca->x7a4}}}["\146\x69e\x6cds"][] = "up\144\141t\145d\x5f\x69\x6e"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->xf1a}}}["\x76\x61\154u\x65\x73"][] = $x2d2(); } $this->queries[$this->queryIndexer][] = "\125\x50\x44\101\124\x45\x20\140" . $this->table . "`\x20\x53E\x54\x20\x0a" . $x2f2(",\x20\x0a", ${$this->x56->x509->{$this->x00->x509->{$this->x67->x509->x167c}}}) . " \n WHERE `entity_id` = '${$this->x67->x4ca->{$this->x56->x4ca->{$this->x67->x4ca->x765}}}';"; } else { ${$this->x00->x4ca->x79f} = []; ${$this->x00->x4ca->{$this->x67->x4ca->{$this->x56->x4ca->x7a4}}}["\x66\151\145\x6cd\163"][] = "\143\162\145\141\164\x65\144\137\141\x74"; ${$this->x00->x509->{$this->x67->x509->x16a9}}["\166\141\154ue\163"][] = "\x27" . $this->{$this->x56->x4ca->{$this->x00->x4ca->x563}}->date("\x59\55m\55\144\x20\150:i:\163") . "\47"; ${$this->x00->x4ca->{$this->x67->x4ca->{$this->x56->x4ca->x7a4}}}["\x66ie\154\x64\163"][] = "\x63r\145a\x74\x65\144\x5fb\x79"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->xf1a}}}["\x76\141l\x75e\x73"][] = "\47" . ${$this->x56->x4f9->{$this->x56->x4f9->xee6}}->{$this->x00->x4ca->xad1}() . "'"; if ($this->_coreHelper->{$this->x56->x4ca->xbee}("\x4d\x61\147\145\156\164\157\x5f\x45\156\164\145\x72p\162\151se")) { ${$this->x67->x509->x16b1}[] = "IN\x53\x45\x52T\x20\x49\x4e\124\117\x20`" . $this->tableSequence . "` \x56\x41\114\125\105\123 (\x73\x65q\165\145\156\143\x65\137\x76\141\154\165\x65=N\125\114\114)\x3b"; ${$this->x67->x4ca->{$this->x67->x4ca->x7ac}}[] = "\123\x45\x54 @p\162o\144\165c\x74_\151d\x3d\114\101\123T_\x49\x4e\123ER\x54\x5f\111\x44\50)\73"; ${$this->x56->x509->x16a5}["\146\x69\145\x6c\x64s"][] = "\145n\x74\x69\x74\x79\137i\144"; ${$this->x00->x509->{$this->x56->x509->{$this->x67->x509->{$this->x56->x509->x16af}}}}["v\x61\x6cue\x73"][] = "\100p\x72\x6f\x64uc\164\137\151d"; ${$this->x00->x509->{$this->x67->x509->x16a9}}["\146ields"][] = "\x63\x72e\141\x74\145\144_i\156"; ${$this->x56->x509->x16a5}["\166a\x6c\165e\163"][] = 1; ${$this->x56->x509->x16a5}["\146\x69\x65\x6cd\x73"][] = "u\x70\x64\141\x74\x65\144\137\151\156"; ${$this->x00->x4ca->{$this->x67->x4ca->x7a2}}["\166al\x75\145\163"][] = $x2d2(); } foreach ($this->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->{$this->x67->x4ca->x51b}}}} as ${$this->x56->x4ca->x786} => ${$this->x00->x4ca->x791}) { ${$this->x00->x4f9->xf15}["f\x69\145\154\144s"][] = ${$this->x67->x4f9->{$this->x67->x4f9->{$this->x67->x4f9->{$this->x56->x4f9->{$this->x00->x4f9->xf02}}}}}; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->{$this->x67->x4f9->xf1f}}}}}["\166a\154u\x65\x73"][] = "\x27" . ${$this->x00->x4ca->{$this->x56->x4ca->{$this->x67->x4ca->{$this->x67->x4ca->x799}}}} . "\47"; } ${$this->x67->x509->x16b1}[] = "\111NS\x45R\124\x20\x49N\x54\117 \140" . $this->table . "\x60 \x28" . $x2f2("\54", ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->xf1a}}}["\x66\x69\x65l\x64s"]) . ")\40VA\114\x55\105\x53\x28" . $x2f2("\54", ${$this->x00->x4ca->{$this->x67->x4ca->{$this->x56->x4ca->{$this->x00->x4ca->x7a7}}}}["\x76a\154\x75es"]) . "\51\73"; ${$this->x56->x4f9->{$this->x00->x4f9->{$this->x67->x4f9->{$this->x00->x4f9->xf28}}}} = $x300(${$this->x67->x509->x16b1}); foreach (${$this->x56->x4f9->{$this->x00->x4f9->{$this->x00->x4f9->xf27}}} as ${$this->x67->x509->x16bf}) { $x307($this->queries[$this->queryIndexer], ${$this->x67->x4ca->{$this->x67->x4ca->x7b3}}); } } parent::{$this->x00->x4ca->xc21}(${$this->x67->x4ca->x761}, ${$this->x56->x509->{$this->x56->x509->{$this->x00->x509->{$this->x00->x509->{$this->x56->x509->x1672}}}}}); }  public function afterCollect() {$x316 = $this->x67->x4f9->x1128; $this->queries[$this->queryIndexer] = $x316($this->queries[$this->queryIndexer], $this->{$this->x56->x4ca->{$this->x67->x4ca->x574}}); parent::{$this->x56->x4ca->xc2f}(); }  public function getDropdown() {$x436 = $this->x67->x509->{$this->x67->x509->{$this->x67->x509->x18af}};  ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xf49}}}} = []; ${$this->x67->x509->{$this->x67->x509->{$this->x00->x509->{$this->x56->x509->x16d5}}}} = ["\142\x61\143\153\145n\x64_\x74\171\x70\x65"]; ${$this->x67->x4f9->{$this->x56->x4f9->xf5c}} = [ ["\145\161" => [ "\x73\164\x61t\x69c", ] ], ]; ${$this->x56->x4f9->{$this->x00->x4f9->xf6c}} = 0; ${$this->x00->x509->x16c6}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x00->x509->x16f2}}}}]['label'] = "\123k\x75"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xf45}}}['Required attributes'][${$this->x56->x509->{$this->x67->x509->x16eb}}]['id'] = "S\171\x73\x74\x65\155/\x73k\165"; ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->x7bf}}}['Required attributes'][${$this->x56->x4f9->{$this->x00->x4f9->xf6c}}]['style'] = "\163\171s\x74e\x6d"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->{$this->x67->x4f9->xf4a}}}}}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x56->x509->{$this->x56->x509->x16f7}}}}}]['type'] = $this->uniqueIdentifier; ${$this->x00->x509->{$this->x67->x509->x16c7}}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x00->x509->x16f2}}}}]['value'] = ""; ${$this->x56->x509->{$this->x67->x509->x16eb}}++; ${$this->x56->x4f9->xf3f}['Required attributes'][${$this->x56->x509->{$this->x67->x509->x16eb}}]['label'] = "\101t\164r\x69\x62ut\x65 se\164"; ${$this->x00->x509->{$this->x67->x509->x16c7}}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->x16ef}}}]["\x69\x64"] = "\x53y\163te\155/a\x74t\162i\x62\x75\164e\137\x73\x65t\x5fi\x64"; ${$this->x00->x509->x16c6}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->x16ef}}}]['style'] = "s\171\163t\145m"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->{$this->x67->x4f9->xf4a}}}}}['Required attributes'][${$this->x00->x4f9->xf67}]['type'] = "\x41t\x74ri\x62u\164\145 se\x74\40\151\144 \x6f\162 \x41\164\164\x72i\x62u\164e s\x65\164\x20n\x61\x6de\40\50\143\141\163e\x20u\x6ese\156\163\x69\x74i\x76e\x29"; ${$this->x67->x4ca->{$this->x56->x4ca->x7bc}}['Required attributes'][${$this->x56->x4ca->{$this->x67->x4ca->x7e6}}]['options'] = $this->{$this->x67->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->{$this->x00->x4ca->x52e}}}}; ${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x00->x509->x16f2}}}}++; ${$this->x00->x509->{$this->x67->x509->x16c7}}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x56->x509->{$this->x56->x509->x16f7}}}}}]['label'] = "\x54yp\x65"; ${$this->x00->x509->{$this->x67->x509->x16c7}}['Required attributes'][${$this->x56->x4f9->{$this->x00->x4f9->xf6c}}]["i\144"] = "S\x79\x73t\x65m/\x74y\160e_\151d"; ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->x7bf}}}['Required attributes'][${$this->x00->x509->x16e6}]['style'] = "s\171s\164\x65\155"; ${$this->x67->x4f9->{$this->x00->x4f9->xf42}}['Required attributes'][${$this->x00->x509->x16e6}]['type'] = "\120\x72o\144\165\143\x74\40T\x79\160\x65 \x49\144 (\x63a\163e \x75\156\163e\x6e\163\151\164\x69ve)"; ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->x7bf}}}['Required attributes'][${$this->x56->x4f9->{$this->x00->x4f9->xf6c}}]['type'] = "\123\151\x6d\x70\154\145\54\40Co\x6efig\x75ra\142\154\x65\x2c\40D\157\x77n\x6coa\144\x61\x62l\x65\54\40V\x69r\164ua\x6c, \102\165ndle,\40G\162\157up\145\144\x2c\40.\x2e."; ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->x7bf}}}['Required attributes'][${$this->x00->x509->x16e6}]['options'] = $this->{$this->x56->x4f9->{$this->x67->x4f9->xd1c}}->{$this->x00->x4ca->xc40}(); ${$this->x00->x4f9->xf67}++; ${$this->x56->x4f9->xf3f}['Required attributes'][${$this->x67->x4ca->x7e2}]['label'] = "\x57\145\x62s\x69\164\145"; ${$this->x67->x4ca->x7b9}['Required attributes'][${$this->x56->x4f9->{$this->x00->x4f9->xf6c}}]["\151d"] = "\x53\x79st\145\155\57w\x65\142\163it\145"; ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->x7bf}}}['Required attributes'][${$this->x00->x4f9->xf67}]['style'] = "\x73\171s\x74e\x6d"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xf45}}}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->x16ef}}}]['type'] = "\x57\x65\x62\x73\151t\x65 i\x64\40\157r \127e\142si\x74\145\x20\156am\x65\x20\50\143\141\x73\145 \165n\x73\145\x6e\x73\151\x74\151v\x65\51"; ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->x7bf}}}['Required attributes'][${$this->x67->x4ca->x7e2}]['options'] = $this->websites; ${$this->x56->x4ca->{$this->x67->x4ca->x7e6}}++; ${$this->x00->x4ca->{$this->x56->x4ca->x7ec}} = $this->{$this->x67->x4ca->xc51}(["\x61\164\164\x72\x69\x62\x75\164\x65\137cod\145"], [["\145\161" => "\x74a\x78\137c\154a\163\x73_\x69\144"]])[0]; ${$this->x00->x509->x16c6}['Required attributes'][${$this->x00->x4f9->xf67}]['label'] = "\124ax\x20\x43l\141\x73\x73"; ${$this->x00->x509->x16c6}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x56->x509->{$this->x56->x509->x16f7}}}}}]["id"] = "A\x74\x74\x72\151\x62\165te/" . ${$this->x00->x509->x16f8}['backend_type'] . "\57" . ${$this->x00->x4ca->{$this->x56->x4ca->x7ec}}['attribute_id'] . "\57t\141\170\x5f\x63\x6ca\163\163\137\x69\144"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xf49}}}}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x56->x509->{$this->x56->x509->x16f7}}}}}]['style'] = "\163\171\163\164em"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xf49}}}}['Required attributes'][${$this->x00->x4f9->xf67}]['type'] = "T\x61\x78cla\163s\x20\x69\144\40\x6fr T\x61\x78\143\154a\x73\x73\40\156am\145 \50\143\141\163e\40\165\156s\x65n\x73i\164\x69\166\145\x29"; ${$this->x00->x509->{$this->x67->x509->x16c7}}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->x16ef}}}]['options'] = $this->{$this->x00->x4f9->xd18}->{$this->x00->x4ca->xc5f}();; ${$this->x00->x4f9->xf67}++; ${$this->x56->x509->{$this->x67->x509->x16fa}} = $this->{$this->x67->x4ca->xc51}(["\141\164\x74\x72\151\x62\165\x74\145\x5fc\x6f\x64\x65"], [["\145\161" => "vi\163ib\x69\x6c\151\164\x79"]])[0]; ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->x7bf}}}['Required attributes'][${$this->x56->x4ca->{$this->x67->x4ca->x7e6}}]['label'] = "V\151s\151\x62\x69l\151\x74\x79"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xf49}}}}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->x16ef}}}]["\151\x64"] = "A\164\164\162\x69\142\165\164\145\57" . ${$this->x00->x509->x16f8}['backend_type'] . "\57" . ${$this->x56->x509->{$this->x67->x509->x16fa}}['attribute_id'] . "\x2fvi\x73i\142\x69\x6c\151\x74y"; ${$this->x67->x4ca->{$this->x56->x4ca->x7bc}}['Required attributes'][${$this->x56->x4f9->{$this->x00->x4f9->xf6c}}]['style'] = "\x73y\x73t\x65\x6d\x20\163t\157\x72\145\x76\151e\167s\55de\160\x65\156\x64\x65nt"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xf45}}}['Required attributes'][${$this->x56->x509->{$this->x67->x509->x16eb}}]['type'] = "P\x72od\x75\143t\40v\151\x73\x69\142\151\x6ci\164y I\144 \x6f\162\x20\x70r\x6fd\165ct\40\166\151\163i\142\x69li\x74y\x20na\x6d\x65 (\x63\x61\163\145 uns\x65n\x73i\164\151\x76\x65\x29"; ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->x7bf}}}['Required attributes'][${$this->x00->x509->x16e6}]['options'] = $this->{$this->x00->x4f9->xd18}->{$this->x67->x4ca->xc75}(); ${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x56->x509->{$this->x56->x509->x16f7}}}}}++; ${$this->x00->x4f9->xf71} = $this->{$this->x67->x4ca->xc51}(["\141t\164\162i\142u\164\145_c\x6f\x64e"], [["\x65q" => "\163\x74\141t\x75\x73"]])[0]; ${$this->x56->x4f9->xf3f}['Required attributes'][${$this->x00->x4f9->xf67}]['label'] = "\x53t\141t\x75\163"; ${$this->x67->x4ca->{$this->x56->x4ca->x7bc}}['Required attributes'][${$this->x56->x509->{$this->x67->x509->x16eb}}]["\151d"] = "\x41\x74\164r\151b\165\164\x65/" . ${$this->x00->x4ca->{$this->x56->x4ca->{$this->x67->x4ca->x7f1}}}['backend_type'] . "\x2f" . ${$this->x56->x509->{$this->x67->x509->x16fa}}['attribute_id'] . "/\163\164\141\164\165s"; ${$this->x00->x509->x16c6}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x00->x509->x16f2}}}}]['style'] = "\163\171s\164\x65m\x20\163\164\x6f\162e\x76\x69ew\163-\144e\x70\145\156den\164"; ${$this->x67->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->x7bf}}}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->x16ef}}}]['type'] = "\x50\x72\x6fdu\143t S\x74\x61t\165\x73\x20(en\141b\154\x65\x64/di\163a\x62l\145\144)"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->{$this->x67->x4f9->xf4a}}}}}['Required attributes'][${$this->x56->x4ca->{$this->x67->x4ca->x7e6}}]['value'] = $x436(",\40", self::ENABLE) . " \157\x72 " . $x436(",\x20", self::DISABLE); ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xf45}}}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x56->x509->{$this->x56->x509->x16f7}}}}}]['options'] = $this->{$this->x56->x4ca->{$this->x56->x4ca->x56c}}->{$this->x67->x4ca->xc9a}(); ${$this->x00->x4f9->xf67}++; ${$this->x56->x509->{$this->x00->x509->{$this->x56->x509->x16fb}}} = $this->{$this->x67->x4ca->xc51}(["at\164\x72ibut\145\137\143o\x64\x65"], [["\x65q" => "u\x72l\x5f\x6b\x65\x79"]])[0]; ${$this->x67->x4ca->x7b9}['Required attributes'][${$this->x56->x509->{$this->x56->x509->{$this->x67->x509->{$this->x56->x509->{$this->x56->x509->x16f7}}}}}]['label'] = "\125\162l\40k\145\171"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xf45}}}['Required attributes'][${$this->x00->x4f9->xf67}]['id'] = "A\164tr\x69\142\x75t\x65\x2f" . ${$this->x56->x509->{$this->x00->x509->{$this->x56->x509->x16fb}}}['backend_type'] . "\x2f" . ${$this->x00->x4ca->{$this->x56->x4ca->x7ec}}['attribute_id'] . "\57\165rl_k\x65y"; ${$this->x00->x509->x16c6}['Required attributes'][${$this->x56->x4f9->{$this->x00->x4f9->xf6c}}]['style'] = "\163t\x61\x74\x69\143 \163\164\x6f\162\x65\166i\145\167\x73\55\x64\145pen\144\145n\164"; ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xf45}}}['Required attributes'][${$this->x00->x509->x16e6}]['type'] = "\x50\x72od\x75\x63\x74\40Url\x20\x6b\x65y"; ${$this->x00->x4f9->xf67}++; return ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x67->x4f9->xf45}}}; }  public function getFields($x49b = null, $x48c = false, $x491 = null) { if (${$this->x00->x4ca->{$this->x67->x4ca->x7fb}} == null) { return true; } ${$this->x56->x4f9->xf7f} = parent::{$this->x56->x4ca->xcb8}(${$this->x67->x4ca->x7f9}, ${$this->x56->x4ca->{$this->x00->x4ca->{$this->x56->x4ca->{$this->x67->x4ca->x802}}}}, ${$this->x00->x509->{$this->x00->x509->x1713}}); ${$this->x67->x4f9->{$this->x56->x4f9->{$this->x56->x4f9->xf88}}}->{$this->x67->x4ca->xcc3}('product_removal', 'select', [ 'name' => 'product_removal', 'label' => __('Automatic action for missing products'), 'note' => 'Action for each product that is missing from the data file', 'options' => [ 0 => 'Do nothing', 1 => 'Disable the product', 2 => 'Delete permanently the product', 3 => 'Mark the product as out of stock', ], ]); ${$this->x00->x4ca->{$this->x67->x4ca->x7fb}}->{$this->x67->x4ca->xcc3}('product_target', 'select', [ 'name' => 'product_target', 'label' => __('Target'), 'note' => 'Target of products impacted by the action' . '<script>
                var selectRemoval = document.getElementById("product_removal");
                var selectTarget = document.getElementsByClassName("field-product_target")[0];
                selectRemoval.addEventListener("change", function () {
                    var value = selectRemoval.options[selectRemoval.selectedIndex].value;
                    if (value != 0) {
                        selectTarget.style.display = "block";
                    } else {
                        selectTarget.style.display = "none";
                    }
                });
                var event = new Event("change");
                selectRemoval.dispatchEvent(event);
            </script>', 'options' => [ 0 => 'Only products related to current profile', 1 => 'Only products not related to current profile', 2 => 'All products' ] ]); return ${$this->x56->x509->x1705}; }  public function addModuleIf($x4a0) { ${$this->x67->x4ca->{$this->x67->x4ca->x817}} = ["S\171\163\x74em"]; return ${$this->x67->x4ca->{$this->x56->x4ca->{$this->x56->x4ca->x81c}}}; }  public function getIndexes($x4ac = []) { return [1 => "ca\x74a\x6cogru\x6c\x65\x5fr\165\154\x65", 2 => "ca\164a\154\157\147\162\165l\x65\137p\x72o\144\x75\x63\164"]; } } 