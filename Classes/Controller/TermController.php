<?php
namespace DPN\DpnGlossary\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Daniel Dorndorf <dorndorf@dreipunktnull.com>, dreipunktnull
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use DPN\DpnGlossary\Domain\Model\Term;
use DPN\DpnGlossary\Domain\Repository\TermRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 *
 *
 * @package dpn_glossary
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class TermController extends ActionController {

	/**
	 * @var TermRepository
	 */
	protected $termRepository;

	/**
	 * @param TermRepository $termRepository
	 * @return void
	 */
	public function injectTermRepository(TermRepository $termRepository) {
		$this->termRepository = $termRepository;
	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
        $terms = 'character' === $this->settings['listmode'] ?
            $this->termRepository->findAllGroupedByFirstCharacter() :
            $this->termRepository->findAll();

		$this->view->assign('detailPage', $this->settings['detailPage']);
        $this->view->assign('listmode', $this->settings['listmode']);
		$this->view->assign('terms', $terms);
	}

	/**
	 * action show
	 *
	 * @param Term $term
	 * @param integer $pageUid
	 * @return void
	 */
	public function showAction(Term $term, $pageUid = NULL) {
		$pageUid = FALSE === empty($pageUid) ? $pageUid : FALSE;

		if ('pagination' === $this->settings['listmode']) {
			$this->view->assign('paginateLink', $this->paginationArguments($term->getName()));
		}

		$this->view->assign('pageUid', $pageUid);
		$this->view->assign('listPage', $this->settings['listPage']);
		$this->view->assign('term', $term);
	}

	/**
	 * If the pagination is used this function
	 * will prepare the link arguments to get
	 * back to the last pagination page
	 *
	 * @param string $termname
	 * @return array
	 */
	private function paginationArguments($termname) {
		$termCharacter = mb_strtoupper(mb_substr($termname,0,1,'UTF-8'), 'UTF-8');
		$characters = array_change_key_case(explode(',',$this->settings['pagination']['characters']), CASE_UPPER);

		/*
		 * Replace umlauts if they are in characters
		 * else use A,O,U
		 */
		$hasUmlauts = array_intersect(array('Ä', 'Ö', 'Ü'), $characters);
		$umlautReplacement = FALSE === empty($hasUmlauts) ?
			array('AE', 'OE', 'UE') :
			array('A', 'O', 'U');

		$termCharacter = str_replace(
			array('Ä', 'Ö', 'Ü'),
			$umlautReplacement,
			$termCharacter
		);

		$characters = str_replace(
			array('Ä', 'Ö', 'Ü'),
			$umlautReplacement,
			$characters
		);

		$character = TRUE === in_array($termCharacter, $characters) ?
			$termCharacter :
			FALSE;

		return array(
			'@widget_0' => array(
				'character' => $character
			)
		);
	}
}
