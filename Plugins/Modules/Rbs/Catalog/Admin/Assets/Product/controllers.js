(function ()
{
	"use strict";

	var app = angular.module('RbsChange');

	function PricesController($scope, $routeParams, $location, REST, i18n)
	{
		$scope.shopId = $routeParams.shopId;
		$scope.areaId = $routeParams.areaId;
		$scope.List = {};

		var query = {
			"model": "Rbs_Catalog_Price",
			"where": {
				"and": [
					{
						"op": "eq",
						"lexp": { "property" : "product" },
						"rexp": { "value": $routeParams.id}
					}/*,
					{
						"op" : "eq",
						"lexp": { "property" : "shop" },
						"rexp": { "value": scope.List.selectedShop.id }
					},
					{
						"op": "eq",
						"lexp": { "property" : "billingArea" },
						"rexp": { "value": newValue.id }
					}*/
				]
			},
			"order": [
				{
					"property": "value",
					"order": "asc"
				}
			]
		};
		$scope.List.query = query;

		var updateLocation = function (newDocId, oldDocId){
			var regexp = new RegExp('/' + oldDocId + '/');
			var path = $location.path();
			if (regexp.test(path))
			{
				var replace = newDocId ? '/' + newDocId + '/' : '/';
				$location.path(path.replace(regexp, replace));
			}
			else
			{
				if (newDocId > 0)
				{
					$location.path(path +  newDocId + '/');
				}
			}
		};

		$scope.changeShop = function(shopId){
			updateLocation(shopId, $routeParams.shopId);
		};

		$scope.changeArea = function(areaId){
			updateLocation(areaId, $routeParams.areaId);
		};
	}

	PricesController.$inject = ['$scope', '$routeParams', '$location', 'RbsChange.REST', 'RbsChange.i18n'];
	app.controller('Rbs_Catalog_Product_PricesController', PricesController);


	/**
	 * Controller for list.
	 *
	 * @param $scope
	 * @param Breadcrumb
	 * @param MainMenu
	 * @param i18n
	 * @constructor
	 */
	function ListController($scope, Breadcrumb, MainMenu, i18n)
	{
		Breadcrumb.resetLocation([
			[i18n.trans('m.rbs.catalog.admin.js.module-name | ucf'), "Rbs/Catalog"],
			[i18n.trans('m.rbs.catalog.admin.js.product-list | ucf'), "Rbs/Catalog/Product"]
		]);

		MainMenu.loadModuleMenu('Rbs_Catalog');
	}

	ListController.$inject = ['$scope', 'RbsChange.Breadcrumb', 'RbsChange.MainMenu', 'RbsChange.i18n'];
	app.controller('Rbs_Catalog_Product_ListController', ListController);

	/**
	 * Controller for form.
	 *
	 * @param $scope
	 * @param Breadcrumb
	 * @param FormsManager
	 * @param i18n
	 * @constructor
	 */
	function FormController($scope, Breadcrumb, FormsManager, i18n)
	{
		Breadcrumb.setLocation([
			[i18n.trans('m.rbs.catalog.admin.js.module-name | ucf'), "Rbs/Catalog"],
			[i18n.trans('m.rbs.catalog.admin.js.product-list | ucf'), "Rbs/Catalog/Product"]
		]);
		FormsManager.initResource($scope, 'Rbs_Catalog_Product');
	}

	FormController.$inject = ['$scope', 'RbsChange.Breadcrumb', 'RbsChange.FormsManager', 'RbsChange.i18n'];
	app.controller('Rbs_Catalog_Product_FormController', FormController);

	/**
	 * Controller for products.
	 *
	 * @param $scope
	 * @param Breadcrumb
	 * @param i18n
	 * @param REST
	 * @param Loading
	 * @param Workspace
	 * @param $routeParams
	 * @param $http
	 * @constructor
	 */
	function CategoriesController($scope, Breadcrumb, i18n, REST, Loading, Workspace, $routeParams, $http)
	{
		Workspace.collapseLeftSidebar();

		Breadcrumb.setLocation([
			[i18n.trans('m.rbs.catalog.admin.js.module-name | ucf'), "Rbs/Catalog"],
			[i18n.trans('m.rbs.catalog.admin.js.product-list | ucf'), "Rbs/Catalog/Product"]
		]);

		$scope.List = {};


		Loading.start(i18n.trans('m.rbs.admin.admin.js.loading-document | ucf'));
		REST.resource('Rbs_Catalog_AbstractProduct', $routeParams.id).then(function (product)
		{
			console.log(product);
			$scope.categoriesUrl = product.META$.links['categories'].href;
			$scope.document = product;
			Loading.stop();
		});


		$scope.List.toggleHighlight = function (doc) {
			var url = null;
			if (doc.position < 0)
			{
				url = doc.META$.actions['downplay'].href;
			}
			else
			{
				url = doc.META$.actions['highlight'].href;
			}
			callActionUrlAndReload(url);
		};


		$scope.$on('$destroy', function () {
			Workspace.restore();
		});

		function callActionUrlAndReload(url)
		{
			if (url)
			{
				$http.get(url).success(function (data)
				{
					$scope.$broadcast('Change:DocumentList:DLRbsCatalogCategoryProducts:call', { 'method' : 'reload' });
				}).error(function errorCallback(data, status)
					{
						data.httpStatus = status;
						$scope.$broadcast('Change:DocumentList:DLRbsCatalogCategoryProducts:call', { 'method' : 'reload' });
					});
			}
		}
	}

	CategoriesController.$inject = ['$scope', 'RbsChange.Breadcrumb', 'RbsChange.i18n', 'RbsChange.REST', 'RbsChange.Loading',
		'RbsChange.Workspace', '$routeParams', '$http'];
	app.controller('Rbs_Catalog_Product_CategoriesController', CategoriesController);

	/**
	 * List actions.
	 */
	app.config(['$provide', function ($provide) {
		$provide.decorator('RbsChange.Actions', ['$delegate', 'RbsChange.REST', '$http', 'RbsChange.i18n', function (Actions, REST, $http, i18n) {
			Actions.register({
				name: 'Rbs_Catalog_RemoveProductFromCategories',
				models: '*',
				description: i18n.trans('m.rbs.catalog.admin.js.remove-product-from-categories'),
				label: i18n.trans('m.rbs.catalog.admin.js.remove'),
				selection: "+",
				execute: ['$docs', '$scope', function ($docs, $scope) {
					var categoryIds = [];
					for (var i in $docs)
					{
						categoryIds.push($docs[i].id);
					}
					var conditionId = $scope.data.conditionId;
					var url = REST.getBaseUrl('catalog/product/' + $scope.data.containerId + '/categories/' + conditionId + '/');
					$http.put(url, {"removeCategoryIds": categoryIds}, REST.getHttpConfig())
						.success(function (data) {
							// TODO use data
							$scope.refresh();
						})
						.error(function errorCallback (data, status) {
							data.httpStatus = status;
							$scope.refresh();
						});
				}]
			});
			return Actions;
		}]);
	}]);
})();