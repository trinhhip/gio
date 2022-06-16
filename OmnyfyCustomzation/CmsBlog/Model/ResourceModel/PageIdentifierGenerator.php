<?php

namespace OmnyfyCustomzation\CmsBlog\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use OmnyfyCustomzation\CmsBlog\Model\ArticleFactory;
use OmnyfyCustomzation\CmsBlog\Model\CategoryFactory;
use OmnyfyCustomzation\CmsBlog\Model\CountryFactory;

/**
 * Page identifier generator
 */
class PageIdentifierGenerator
{

    /**
     * @var ArticleFactory
     */
    protected $_articleFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Construct
     *
     * @param ArticleFactory $articleFactory
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        ArticleFactory $articleFactory, CategoryFactory $categoryFactory, CountryFactory $countryFactory
    )
    {
        $this->_articleFactory = $articleFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_countryFactory = $countryFactory;
    }

    public function generate(AbstractModel $object)
    {
        if ($object->getId() && $object instanceof \OmnyfyCustomzation\CmsBlog\Model\Category) {
            $categoryData = $this->_categoryFactory->create()->load($object->getId());
            if ($categoryData->getIdentifier() == $object->getData('identifier')) {
                return;
            }
        }

        //$identifier = trim($object->getData('title'));
        $identifier = trim($object->getTitle());
        if (!$identifier) {
            return;
        }

        $from = [
            '�?', 'À', 'Â', 'Ä', 'Ă', 'Ā', 'Ã', 'Å', 'Ą', 'Æ', 'Ć', 'Ċ', 'Ĉ', 'Č', 'Ç', 'Ď', '�?', '�?', 'É', 'È', 'Ė', 'Ê', 'Ë', 'Ě', 'Ē', 'Ę', '�?', 'Ġ', 'Ĝ', 'Ğ', 'Ģ', 'á', 'à', 'â', 'ä', 'ă', '�?', 'ã', 'å', 'ą', 'æ', 'ć', 'ċ', 'ĉ', '�?', 'ç', '�?', 'đ', 'ð', 'é', 'è', 'ė', 'ê', 'ë', 'ě', 'ē', 'ę', 'ə', 'ġ', '�?', 'ğ', 'ģ', 'Ĥ', 'Ħ', 'I', '�?', 'Ì', 'İ', 'Î', '�?', 'Ī', 'Į', 'Ĳ', 'Ĵ', 'Ķ', 'Ļ', '�?', 'Ń', 'Ň', 'Ñ', 'Ņ', 'Ó', 'Ò', 'Ô', 'Ö', 'Õ', '�?', 'Ø', 'Ơ', 'Œ', 'ĥ', 'ħ', 'ı', 'í', 'ì', 'i', 'î', 'ï', 'ī', 'į', 'ĳ', 'ĵ', 'ķ', 'ļ', 'ł', 'ń', 'ň', 'ñ', 'ņ', 'ó', 'ò', 'ô', 'ö', 'õ', 'ő', 'ø', 'ơ', 'œ', 'Ŕ', 'Ř', 'Ś', 'Ŝ', 'Š', 'Ş', 'Ť', 'Ţ', 'Þ', 'Ú', 'Ù', 'Û', 'Ü', 'Ŭ', 'Ū', 'Ů', 'Ų', 'Ű', 'Ư', 'Ŵ', '�?', 'Ŷ', 'Ÿ', 'Ź', 'Ż', 'Ž', 'ŕ', 'ř', 'ś', '�?', 'š', 'ş', 'ß', 'ť', 'ţ', 'þ', 'ú', 'ù', 'û', 'ü', 'ŭ', 'ū', 'ů', 'ų', 'ű', 'ư', 'ŵ', 'ý', 'ŷ', 'ÿ', 'ź', 'ż', 'ž',
            '�?', 'Б', 'В', 'Г', 'Д', 'Е', '�?', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', '�?', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', '�?', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', '�?', 'ю', '�?',
            'І', 'і', 'Ї', 'ї', 'Є', 'є',
            ' & ', '&',
        ];

        $to = [
            'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'C', 'C', 'C', 'C', 'D', 'D', 'D', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'G', 'G', 'G', 'G', 'G', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'c', 'c', 'c', 'c', 'd', 'd', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'g', 'g', 'g', 'g', 'g', 'H', 'H', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'IJ', 'J', 'K', 'L', 'L', 'N', 'N', 'N', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'CE', 'h', 'h', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'ij', 'j', 'k', 'l', 'l', 'n', 'n', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'R', 'R', 'S', 'S', 'S', 'S', 'T', 'T', 'T', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'W', 'Y', 'Y', 'Y', 'Z', 'Z', 'Z', 'r', 'r', 's', 's', 's', 's', 'B', 't', 't', 'b', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'w', 'y', 'y', 'y', 'z', 'z', 'z',
            'A', 'B', 'V', 'H', 'D', 'e', 'Io', 'Z', 'Z', 'Y', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'Ch', 'C', 'Ch', 'Sh', 'Shtch', '', 'Y', '', 'E', 'Iu', 'Ia',
            'a', 'b', 'v', 'h', 'd', 'e', 'io', 'z', 'z', 'y', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'ch', 'c', 'ch', 'sh', 'shtch', '', 'y', '', 'e', 'iu', 'ia',
            'I', 'i', 'Ji', 'ji', 'Je', 'je',
            '-and-', '-and-',
        ];

        $identifier = str_replace($from, $to, $identifier);
        $identifier = mb_strtolower($identifier);
        $identifier = preg_replace('/[^A-Za-z0-9-]+/', '-', $identifier);
        $identifier = preg_replace('/[--]+/', '-', $identifier);

        $identifier = trim($identifier, '-');

        $article = $this->_articleFactory->create();
        $category = $this->_categoryFactory->create();
        $country = $this->_countryFactory->create();

        $number = 1;
        while (true) {

            $finalIdentifier = $identifier . ($number > 1 ? '-' . $number : '');

            $articleId = $article->checkIdentifier($finalIdentifier, $object->getStoreId());
            $categoryId = $category->checkIdentifier($finalIdentifier, $object->getStoreId());
            $categoryFoundCount = $category->checkIdentifierCount($finalIdentifier, $object->getStoreId());
            $countryId = $country->checkIdentifier($finalIdentifier, $object->getStoreId());
            //$countryId = $country->getId();

            if (!$articleId && !$categoryId && !$countryId) {
                break;
            } else {
                if ($articleId && $articleId == $object->getId() && $object instanceof \OmnyfyCustomzation\CmsBlog\Model\Article
                ) {
                    break;
                }

                if ($categoryId && $categoryId == $object->getId() && $object instanceof \OmnyfyCustomzation\CmsBlog\Model\Category
                ) {
                    break;
                } else if ($categoryFoundCount) {
                    $identifier = str_replace($from, $to, $identifier);
                    $identifier = mb_strtolower($identifier);
                    $identifier = preg_replace('/[^A-Za-z0-9-]+/', '-', $identifier);
                    $identifier = preg_replace('/[--]+/', '-', $identifier);

                    $identifier = trim($identifier, '-');
                    $finalIdentifier = $this->checkIdentifierExist($identifier, $object->getStoreId());
//					$finalIdentifier = $identifier.'-'.$categoryFoundCount; // india-3
//					if(!$this->checkIdentifierExist($finalIdentifier, $object->getStoreId())){
//						$finalIdentifier = $identifier.'-'.$categoryFoundCount;
//					}
                    break;
                }
                if ($countryId && $countryId == $object->getId() && $object instanceof \OmnyfyCustomzation\CmsBlog\Model\Country
                ) {
                    break;
                }
            }

            $number++;
        }

        $object->setData('identifier', $finalIdentifier);
    }

    public function checkIdentifierExist($identifier, $storeIds)
    {
        $category = $this->_categoryFactory->create();
        $categoryCounter = $category->checkIdentifierCount($identifier, $storeIds);
        if ($categoryCounter > 1) {
            $lastVal = substr(strrchr($identifier, "-"), 1);
            if (is_int($lastVal)) {
                $identifier = str_replace($lastVal, $categoryCounter, $identifier);
                /* exit;
                $identifier = $identifier . '-' . $categoryCounter; */
                $identifier = $this->checkIdentifierExist($identifier, $storeIds);
            } else {
                $identifier = $identifier . '-' . $categoryCounter;
                $identifier = $this->checkIdentifierExist($identifier, $storeIds);
            }
        }
        return $identifier;
    }

}
