<?php

namespace Awaresoft\Sonata\PageBundle\Model;

use Awaresoft\Sonata\PageBundle\Entity\Block;
use Sonata\PageBundle\Entity\BasePage;

/**
 * Extended Page Model, connected with CMS block
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
abstract class Page extends BasePage
{

    const BLOCK_NAME = 'CMS Content';
    const BLOCK_TYPE = 'sonata.formatter.block.formatter';
    const CONTAINER_NAME = 'content';
    const CONTAINER_TYPE = 'sonata.page.block.container';
    const CONTAINER_CODE = 'content';

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $contentFormatter;

    /**
     * @var string
     */
    protected $rawContent;

    /**
     * @var Block
     */
    protected $contentBlock;

    /**
     * Get content from cms block if not set
     *
     * @return mixed|null
     */
    public function getContent()
    {
        if (!$this->content) {
            $block = $this->getContentBlock();

            if (!$block) {
                return null;
            }

            return $block->getSetting('content');
        }

        return $this->content;
    }

    /**
     * Set content to cms block
     *
     * @param $content
     * @param bool|false $snapshot
     */
    public function setContent($content, $snapshot = false)
    {
        $contentBlock = $this->getContentBlock();

        if (!$contentBlock) {
            if ($snapshot) {
                $this->content = $content;

                return;
            }

            $contentBlock = $this->createContentBlock();
        }

        $this->content = $content;
        $contentBlock->setSetting('content', $content);
    }

    /**
     * @return string
     */
    public function getContentFormatter()
    {
        $block = $this->getContentBlock();

        if (!$block) {
            return null;
        }

        return $block->getSetting('format');
    }

    /**
     * @param string $contentFormatter
     */
    public function setContentFormatter($contentFormatter)
    {
        $contentBlock = $this->getContentBlock();

        if (!$contentBlock) {
            $contentBlock = $this->createContentBlock();
        }

        $this->contentFormatter = $contentFormatter;
        $contentBlock->setSetting('format', $contentFormatter);
    }

    /**
     * @return string
     */
    public function getRawContent()
    {
        $block = $this->getContentBlock();

        if (!$block) {
            return null;
        }

        return $block->getSetting('rawContent');
    }

    /**
     * @param string $rawContent
     */
    public function setRawContent($rawContent)
    {
        $contentBlock = $this->getContentBlock();

        if (!$contentBlock) {
            $contentBlock = $this->createContentBlock();
        }

        $this->rawContent = $rawContent;
        $contentBlock->setSetting('rawContent', $rawContent);
    }

    /**
     * @return Block|null
     */
    protected function getContentBlock()
    {
        if (!$this->contentBlock) {
            $this->contentBlock = $this->getBlockByName(self::BLOCK_NAME);
        }

        return $this->contentBlock;
    }

    /**
     * @return Block|null
     */
    protected function createContentBlock()
    {
        $container = $this->getBlockByName(self::CONTAINER_NAME);

        if (!$container) {
            $container = new Block();
            $container->setName(self::CONTAINER_NAME);
            $container->setType(self::CONTAINER_TYPE);
            $container->setSetting('code', self::CONTAINER_CODE);
            $container->setEnabled(true);
            $container->setPosition(1);

            $this->addBlocks($container);
        }

        $block = new Block();
        $block->setParent($container);
        $block->setName(self::BLOCK_NAME);
        $block->setType(self::BLOCK_TYPE);
        $block->setSetting('format', 'richhtml');
        $block->setEnabled(true);
        $block->setPosition(1);

        $this->addBlocks($block);

        return $block;
    }

    /**
     * @param $blockName
     * @return Block|null
     */
    protected function getBlockByName($blockName)
    {
        foreach ($this->blocks as $block) {
            if ($block->getName() == $blockName) {
                return $block;
            }
        }

        return null;
    }

}
