<?php

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum InvoiceType: string implements TranslatableInterface
{
    case INVOICE = 'invoice';
    case CREDIT_NOTE = 'credit_note';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return 'invoice.type.'.$this->value;
    }
}

final class TranslatableTitle implements TranslatableInterface
{
    private string $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('invoice.title.'.$this->title, [], 'messages', $locale);
    }
}

final class TranslatedInvoice
{
    public int $id;

    public TranslatableTitle $title;

    public string $description;

    public InvoiceType $type;
}