<?php
namespace Rbs\Seo\Std;

/**
 * @name \Rbs\Seo\Std\MetaComposer
 */
class MetaComposer
{
	/**
	 * @param \Zend\EventManager\Event $event
	 */
	public function onGetMetas(\Zend\EventManager\Event $event)
	{
		$page = $event->getParam('page');
		$document = $event->getParam('document');
		/* @var $seoManager \Rbs\Seo\Services\SeoManager */
		$seoManager = $event->getTarget();
		if ($page instanceof \Change\Presentation\Interfaces\Page && $document instanceof \Change\Documents\Interfaces\Publishable)
		{
			$metas = [ 'title' => null, 'description' => null, 'keywords' => null ];

			$documentServices = $document->getDocumentServices();
			$documentSeoModel = $documentServices->getModelManager()->getModelByName('Rbs_Seo_DocumentSeo');
			$dqb = new \Change\Documents\Query\Query($documentServices, $documentSeoModel);
			$dqb->andPredicates($dqb->eq('target', $page));
			$pageSeo = $dqb->getFirstDocument();
			$documentRegExp = '/\{(document\.[a-z][A-Za-z0-9.]*)\}/';
			if ($pageSeo)
			{
				/* @var $pageSeo \Rbs\Seo\Documents\DocumentSeo */
				$pageRegExp = '/\{(page\.[a-z][A-Za-z0-9.]*)\}/';
				$pageVariables = $this->getAllVariables($pageRegExp, $pageSeo);
				$pageSubstitutions = (count($pageVariables)) ? $seoManager->getMetaSubstitutions($page, $pageVariables) : [];
				$documentVariables = $this->getAllVariables($documentRegExp, $pageSeo);
				$documentSubstitutions = (count($documentVariables)) ? $this->getDocumentMetaSubstitution($document, $documentRegExp, $documentVariables, $seoManager) : [];

				$metaTitle = $pageSeo->getCurrentLocalization()->getMetaTitle();
				if ($metaTitle)
				{
					$metaTitle = count($pageSubstitutions) ? $this->getSubstituedString($metaTitle, $pageRegExp, $pageSubstitutions) : $metaTitle;
					$metaTitle = count($documentSubstitutions) ? $this->getSubstituedString($metaTitle, $documentRegExp, $documentSubstitutions) : $metaTitle;
					$metas['title'] = $metaTitle;
				}
				else
				{
					$metas['title'] = $document->getDocumentModel()->getPropertyValue($document, 'title');
				}
				$metaDescription = $pageSeo->getCurrentLocalization()->getMetaDescription();
				if ($metaDescription)
				{
					$metaDescription = count($pageSubstitutions) ? $this->getSubstituedString($metaDescription, $pageRegExp, $pageSubstitutions) : $metaDescription;
					$metaDescription = count($documentSubstitutions) ? $this->getSubstituedString($metaDescription, $documentRegExp, $documentSubstitutions) : $metaDescription;
					$metas['description'] = $metaDescription;
				}
				$metaKeywords = $pageSeo->getCurrentLocalization()->getMetaKeywords();
				if ($metaKeywords)
				{
					$metaKeywords = count($pageSubstitutions) ? $this->getSubstituedString($metaKeywords, $pageRegExp, $pageSubstitutions) : $metaKeywords;
					$metaKeywords = count($documentSubstitutions) ? $this->getSubstituedString($metaKeywords, $documentRegExp, $documentSubstitutions) : $metaKeywords;
					$metas['keywords'] = $metaKeywords;
				}
			}
			else
			{
				$dqb = new \Change\Documents\Query\Query($documentServices, $documentSeoModel);
				$dqb->andPredicates($dqb->eq('target', $document));
				$documentSeo = $dqb->getFirstDocument();
				/* @var $documentSeo \Rbs\Seo\Documents\DocumentSeo */
				if ($documentSeo)
				{
					$documentVariables = $this->getAllVariables($documentRegExp, $documentSeo);
					$documentSubstitutions = (count($documentVariables)) ? $seoManager->getMetaSubstitutions($document, $documentVariables) : [];

					$metaTitle = $documentSeo->getCurrentLocalization()->getMetaTitle();
					if ($metaTitle)
					{
						$metaTitle = $this->getSubstituedString($documentSeo->getCurrentLocalization()->getMetaTitle(), $documentRegExp, $documentSubstitutions);
					}
					else
					{
						$metaTitle = $document->getDocumentModel()->getPropertyValue($document, 'title');
					}
					$metas['title'] = $metaTitle;

					$metaDescription = $documentSeo->getCurrentLocalization()->getMetaDescription();
					if ($metaDescription)
					{
						$metas['description'] = $this->getSubstituedString($metaDescription, $documentRegExp, $documentSubstitutions);
					}

					$metaKeywords = $documentSeo->getCurrentLocalization()->getMetaKeywords();
					if ($metaKeywords)
					{
						$metas['keywords'] = $this->getSubstituedString($metaKeywords, $documentRegExp, $documentSubstitutions);
					}
				}
				else
				{
					$metas['title'] = $document->getDocumentModel()->getPropertyValue($document, 'title');
				}
			}
			$event->setParam('metas', $metas);
		}
	}

	/**
	 * @param string $meta
	 * @param string $regExp
	 * @param array $substitutions
	 * @return mixed
	 */
	protected function getSubstituedString($meta, $regExp, $substitutions)
	{
		if (!$meta)
		{
			return $meta;
		}
		return preg_replace_callback($regExp, function ($matches) use ($substitutions)
		{
			if (array_key_exists($matches[1], $substitutions))
			{
				return $substitutions[$matches[1]];
			}
			return '';
		}, $meta);
	}

	/**
	 * @param string $regExp
	 * @param \Rbs\Seo\Documents\DocumentSeo $documentSeo
	 * @return array
	 */
	protected function getAllVariables($regExp, $documentSeo)
	{
		$matches = [];
		preg_match_all($regExp, $documentSeo->getCurrentLocalization()->getMetaTitle(), $matches);
		$variables = $matches[1];
		preg_match_all($regExp, $documentSeo->getCurrentLocalization()->getMetaDescription(), $matches);
		$variables = array_merge($variables, $matches[1]);
		preg_match_all($regExp, $documentSeo->getCurrentLocalization()->getMetaKeywords(), $matches);
		$variables = array_merge($variables, $matches[1]);
		return $variables;
	}

	/**
	 * @param \Change\Documents\AbstractDocument $document
	 * @param string $regExp
	 * @param string[] $variables
	 * @param \Rbs\Seo\Services\SeoManager $seoManager
	 * @return array
	 */
	protected function getDocumentMetaSubstitution($document, $regExp, $variables, $seoManager)
	{
		/* @var $documentSeo \Rbs\Seo\Documents\DocumentSeo */
		$documentSeo = null;
		if (in_array('document.title', $variables) || in_array('document.description', $variables) || in_array('document.keywords', $variables))
		{
			$dqb = new \Change\Documents\Query\Query($document->getDocumentServices(), 'Rbs_Seo_DocumentSeo');
			$dqb->andPredicates($dqb->eq('target', $document));
			$documentSeo = $dqb->getFirstDocument();
			if ($documentSeo)
			{
				$variables = array_merge($variables, $this->getAllVariables($regExp, $documentSeo));
			}
		}

		$substitutions = [];
		if (count($variables))
		{
			$substitutions = $seoManager->getMetaSubstitutions($document, $variables);
		}

		if ($documentSeo)
		{
			$seoSubstitutions = [];
			$metaTitle = $documentSeo->getCurrentLocalization()->getMetaTitle();
			if ($metaTitle)
			{
				$seoSubstitutions['document.title'] = $this->getSubstituedString($metaTitle, $regExp, $substitutions);
			}
			$metaDescription = $documentSeo->getCurrentLocalization()->getMetaDescription();
			if ($metaDescription)
			{
				$seoSubstitutions['document.description'] = $this->getSubstituedString($metaDescription, $regExp, $substitutions);
			}
			$metaKeywords = $documentSeo->getCurrentLocalization()->getMetaKeywords();
			if ($metaKeywords)
			{
				$seoSubstitutions['document.keywords'] = $this->getSubstituedString($metaKeywords, $regExp, $substitutions);
			}
			$substitutions = array_merge($substitutions, $seoSubstitutions);
		}
		return $substitutions;
	}
}