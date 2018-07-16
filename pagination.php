<?php
/**
 * @author: But Oleksandr
 * Date: 15.06.2018
 */

namespace Core\Helper;

class Pagination {

	const DEFAULT_OFFSET = 50;
	const OFFSET_USERS = self::DEFAULT_OFFSET;
	const OFFSET_REQUESTS = self::DEFAULT_OFFSET;
	const OFFSET_TODO_HISTORY = self::DEFAULT_OFFSET;
	const OFFSET_SELF_HISTORY = 150;

	private $Data = [];
	private $currentPage = 1;
	private $name = 'default';
	private $totalItems = 0;

	/**
	 * Pagination constructor.
	 *
	 * @param string $name
	 */
	public function __construct($name = 'default') {
		if ($name !== 'default') {
			$this->name = $name;
		}
		$this->updateCurrentPage();
		$this->Data = $_REQUEST;
	}

	/**
	 * Make updates based on $_GET['page'] request
	 */
	protected function updateCurrentPage() {
		$page = is_numeric($this->Data[$this->name . '_page']) ? (int) $this->Data[$this->name . '_page'] : 1;
		if ($page < 1 || $this->Data[$this->name . '_on_page']) {
			$page = 1;
		}
		$this->currentPage = $page;
	}

	/**
	 * @return string
	 */
	public function getHeader() {
		$offset = $this->getOffset();
		$currPosition = ($this->currentPage - 1) * $offset;
		$lastPosition = ($currPosition + $offset > $this->totalItems ? $this->totalItems : $currPosition + $offset);

		return sprintf('%d - %d из %d', $currPosition + 1, $lastPosition, $this->totalItems);
	}

	/**
	 * @return integer
	 */
	protected function getOffset(): int {
		$this->updateCurrentPage();
		$offset = self::DEFAULT_OFFSET;
		if (defined('self::OFFSET_' . strtoupper($this->name))) {
			$offset = constant('self::' . 'OFFSET_' . strtoupper($this->name));
		}

		if ($this->Data[$this->name . '_on_page'] && is_numeric($this->Data[$this->name . '_on_page'])) {
			$offset = (int) $this->Data[$this->name . '_on_page'];
		}

		return (int) $offset;
	}

	/**
	 * @return integer
	 */
	public function getStartCounter(): int {
		return (int) ($this->currentPage - 1) * $this->getOffset();
	}

	/**
	 * For SQL purposes
	 *
	 * @return string
	 */
	public function getSqlPaginationLimit() {
		$offset = $this->getOffset();

		return sprintf('%d, %d', $this->currentPage == 1 ? 0 : ($this->currentPage - 1) * $offset, $offset);
	}

	/**
	 * Makes slice of array based on $_GET['page'] request
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public function sliceArray(array $array) {
		$offset = $this->getOffset();

		return array_slice($array, $this->currentPage == 1 ? 0 : ($this->currentPage - 1) * $offset, $offset, true);
	}

	/**
	 * Returns pagination string
	 *
	 * @param integer $totalCount
	 *
	 * @return string
	 */
	public function buildPagination(int $totalCount) {
		$this->totalItems = $totalCount;
		$this->updateCurrentPage();
		$offset = $this->getOffset();

		if ($totalCount < $offset) {
			return '';
		}

		$result = '';
		$previous = $this->currentPage - 1;
		$next = $this->currentPage + 1;
		$allPages = (int) ceil($totalCount / $offset);
		$pagination = '<pagination><ul class="pagination pagination-sm">%s</ul></pagination>';
		$itemTpl = '<li class="%s"><a href="%s">%s</a></li>';

		$prevTpl = '<li class="%1$s">
						<a href="%3$s" aria-label="First">
							<span aria-hidden="true">&larr; Начало</span>
						</a>
					</li>
					<li class="%1$s">
						<a href="%2$s" aria-label="Previous">
							<span aria-hidden="true">&laquo;</span>
						</a>
					</li>';

		$nextTpl = '<li class="%1$s">
						<a href="%2$s" aria-label="Next">
							<span aria-hidden="true">&raquo;</span>
						</a>
					</li>
					<li class="%1$s">
						<a href="%3$s" aria-label="Last">
							<span aria-hidden="true">Конец &rarr;</span>
						</a>
					</li>';

		$firstDisabled = false;
		$lastDisabled = false;
		if ($this->currentPage == 1) {
			$firstDisabled = true;
		}
		if ($this->currentPage == $allPages) {
			$lastDisabled = true;
		}

		for ($i = 1; $i <= $allPages; $i++) {
			if ($i <= ($this->currentPage - 5) || $i >= ($this->currentPage + 5)) {
				continue;
			}
			$result .= sprintf($itemTpl, ($i === $this->currentPage ? 'active' : ''), $this->buildURL(false, $i), $i);
		}
		$result = sprintf(
				$prevTpl,
				$firstDisabled ? 'disabled' : '',
				$this->buildURL($firstDisabled, $previous),
				$this->buildURL($firstDisabled, 1)
			) . $result;

		$result .= sprintf(
			$nextTpl,
			$lastDisabled ? 'disabled' : '',
			$this->buildURL($lastDisabled, $next),
			$this->buildURL($lastDisabled, $allPages)
		);

		return sprintf($pagination, $result) . $this->addShowAllButton();
	}

	/**
	 * Makes unset of $_GET[*_page]
	 */
	protected function buildGetRequest() {
		$result = $_GET;
		foreach ($result as $key => $val) {
			if (preg_match('/^[a-z]+_page/', $key)) {
				unset($result[$key]);
			}
		}

		return $result;
	}

	/**
	 * @param boolean $isDisabled
	 * @param integer $page
	 *
	 * @return string
	 */
	protected function buildURL(bool $isDisabled, int $page): string {
		$query = http_build_query($this->buildGetRequest());
		if ($isDisabled) {
			return '#';
		}

		return sprintf('?%s&%s_page=%d', $query, $this->name, $page);
	}

	/**
	 * @return string
	 */
	protected function addShowAllButton() {
		$query = http_build_query($this->buildGetRequest());
		$url = sprintf('?%s&%s_on_page=%d', $query, $this->name, $this->totalItems);
		$template = ' <ul class="pagination pagination-sm"><li class="list-blue"><a href="%s" aria-label="Show all"><span aria-hidden="false">Показать все</span></a></li></ul>';

		return sprintf($template, $url);
	}
}
