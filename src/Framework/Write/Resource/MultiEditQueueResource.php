<?php declare(strict_types=1);

namespace Shopware\Framework\Write\Resource;

use Shopware\Framework\Write\Flag\Required;
use Shopware\Framework\Write\Field\FkField;
use Shopware\Framework\Write\Field\IntField;
use Shopware\Framework\Write\Field\ReferenceField;
use Shopware\Framework\Write\Field\StringField;
use Shopware\Framework\Write\Field\BoolField;
use Shopware\Framework\Write\Field\DateField;
use Shopware\Framework\Write\Field\SubresourceField;
use Shopware\Framework\Write\Field\LongTextField;
use Shopware\Framework\Write\Field\LongTextWithHtmlField;
use Shopware\Framework\Write\Field\FloatField;
use Shopware\Framework\Write\Field\TranslatedField;
use Shopware\Framework\Write\Field\UuidField;
use Shopware\Framework\Write\Resource;

class MultiEditQueueResource extends Resource
{
    protected const RESOURCE_FIELD = 'resource';
    protected const FILTER_STRING_FIELD = 'filterString';
    protected const OPERATIONS_FIELD = 'operations';
    protected const ITEMS_FIELD = 'items';
    protected const ACTIVE_FIELD = 'active';
    protected const CREATED_FIELD = 'created';

    public function __construct()
    {
        parent::__construct('s_multi_edit_queue');
        
        $this->fields[self::RESOURCE_FIELD] = (new StringField('resource'))->setFlags(new Required());
        $this->fields[self::FILTER_STRING_FIELD] = (new LongTextField('filter_string'))->setFlags(new Required());
        $this->fields[self::OPERATIONS_FIELD] = (new LongTextField('operations'))->setFlags(new Required());
        $this->fields[self::ITEMS_FIELD] = (new IntField('items'))->setFlags(new Required());
        $this->fields[self::ACTIVE_FIELD] = new BoolField('active');
        $this->fields[self::CREATED_FIELD] = new DateField('created');
        $this->fields['articless'] = new SubresourceField(\Shopware\Framework\Write\Resource\MultiEditQueueArticlesResource::class);
    }
    
    public function getWriteOrder(): array
    {
        return [
            \Shopware\Framework\Write\Resource\MultiEditQueueResource::class,
            \Shopware\Framework\Write\Resource\MultiEditQueueArticlesResource::class
        ];
    }
}
