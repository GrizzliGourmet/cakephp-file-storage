<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Model\Entity;

use Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait;
use Cake\Event\EventDispatcherTrait;
use Cake\ORM\Entity;

/**
 * FileStorage Entity.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
class FileStorage extends Entity
{
    use EventDispatcherTrait;
    use PathBuilderTrait;

    /**
     * @var array
     * {@inheritdoc}
     */
    protected $_virtual = [
        'url',
        'full_path',
    ];

    /**
     * @var array
     * Path Builder Class.
     *
     * This is named $_pathBuilderClass because $_pathBuilder is already used by
     * the trait to store the path builder instance.
     */
    protected $_pathBuilderClass = null;

    /**
     * @var array
     * Path Builder options
     */
    protected $_pathBuilderOptions = [];

    /**
     * Constructor
     *
     * @param array $properties hash of properties to set in this entity
     * @param array $options list of options to use when creating this entity
     */
    public function __construct(array $properties = [], array $options = [])
    {
        $options += [
            'pathBuilder' => $this->_pathBuilderClass,
            'pathBuilderOptions' => $this->_pathBuilderOptions,
        ];

        parent::__construct($properties, $options);

        if (!empty($options['pathBuilder'])) {
            $this->pathBuilder(
                $options['pathBuilder'],
                $options['pathBuilderOptions']
            );
        }
    }

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
    ];

    /**
     * Accessor to get the *real* path on disk / backend + filename.
     *
     * @link http://book.cakephp.org/3.0/en/orm/entities.html#accessors-mutators
     * @return string
     */
    protected function _getFullPath(): string
    {
        return $this->path();
    }

    /**
     * Accessor to get the URL to this file.
     *
     * @link http://book.cakephp.org/3.0/en/orm/entities.html#accessors-mutators
     * @return string
     */
    protected function _getUrl(): string
    {
        return $this->url();
    }

    /**
     * Gets a path for this entities file.
     *
     * @param array $options Path options.
     * @return string
     */
    public function path(array $options = []): string
    {
        if (empty($options['method'])) {
            $options['method'] = 'fullPath';
        }

        return $this->_path($options);
    }

    /**
     * Gets an URL for this entities file.
     *
     * @param array $options Path options.
     * @return string
     */
    public function url(array $options = []): string
    {
        $options['method'] = 'url';

        return $this->_path($options);
    }

    /**
     * Gets a path for this entities file.
     *
     * @param array $options Path options.
     * @return string
     */
    protected function _path(array $options): string
    {
        if (empty($options['method'])) {
            $options['method'] = 'path';
        }

        $options['entity'] = $this;
        $event = $this->dispatchEvent('FileStorage.path', $options);

        return $event->getResult();
    }
}
