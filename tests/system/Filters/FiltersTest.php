<?php namespace CodeIgniter\Filters;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Services;

class FiltersTest extends \CIUnitTestCase
{
	protected $requeset;
	protected $response;

	public function __construct()
	{
	    parent::__construct();

		$this->request = Services::request();
		$this->response = Services::response();
	}

	//--------------------------------------------------------------------

	public function setUp()
	{

	}

	//--------------------------------------------------------------------

	public function tearDown()
	{

	}

	//--------------------------------------------------------------------

	public function testProcessMethodDetectsCLI()
	{
		$config = [
			'methods' => [
				'cli' => ['foo']
			]
		];
		$filters = new Filters((object)$config, $this->request, $this->response);

		$expected = [
			'before' => ['foo'],
			'after'  => []
		];

		$this->assertEquals($expected, $filters->initialize()->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodDetectsGetRequests()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'methods' => [
				'get' => ['foo']
			]
		];
		$filters = new Filters((object)$config, $this->request, $this->response);

		$expected = [
			'before' => ['foo'],
			'after'  => []
		];

		$this->assertEquals($expected, $filters->initialize()->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodRespectsMethod()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'methods' => [
				'post' => ['foo'],
				'get'  => ['bar']
			]
		];
		$filters = new Filters((object)$config, $this->request, $this->response);

		$expected = [
			'before' => ['bar'],
			'after'  => []
		];

		$this->assertEquals($expected, $filters->initialize()->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodProcessGlobals()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'globals' => [
				'before' => [
					'foo' => ['bar'],
					'bar'
				],
				'after' => [
					'baz'
				]
			]
		];
		$filters = new Filters((object)$config, $this->request, $this->response);

		$expected = [
			'before' => [
				'foo' => ['bar'],
				'bar'
			],
			'after'  => ['baz']
		];

		$this->assertEquals($expected, $filters->initialize()->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodProcessGlobalsWithExcept()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'globals' => [
				'before' => [
					'foo' => ['except' => ['admin/*']],
					'bar'
				],
				'after' => [
					'baz'
				]
			]
		];
		$filters = new Filters((object)$config, $this->request, $this->response);
		$uri = 'admin/foo/bar';

		$expected = [
			'before' => [
				'bar'
			],
			'after'  => ['baz']
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodProcessesFiltersBefore()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'filters' => [
				'foo' => ['before' => ['admin/*'], 'after' => ['/users/*']]
			]
		];
		$filters = new Filters((object)$config, $this->request, $this->response);
		$uri = 'admin/foo/bar';

		$expected = [
			'before' => ['foo'],
			'after'  => []
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodProcessesFiltersAfter()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'filters' => [
				'foo' => ['before' => ['admin/*'], 'after' => ['/users/*']]
			]
		];
		$filters = new Filters((object)$config, $this->request, $this->response);
		$uri = 'users/foo/bar';

		$expected = [
			'before' => [],
			'after'  => ['foo']
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodProcessesCombined()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'globals' => [
				'before' => [
					'foog' => ['except' => ['admin/*']],
					'barg'
				],
				'after' => [
					'bazg'
				]
			],
			'methods' => [
				'post' => ['foo'],
				'get'  => ['bar']
			],
			'filters' => [
				'foof' => ['before' => ['admin/*'], 'after' => ['/users/*']]
			]
		];
		$filters = new Filters((object)$config, $this->request, $this->response);
		$uri = 'admin/foo/bar';

		$expected = [
			'before' => ['barg', 'bar', 'foof'],
			'after'  => ['bazg']
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	//--------------------------------------------------------------------
}