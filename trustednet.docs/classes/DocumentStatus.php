<?php

class DocumentStatus implements IEntity, ISave
{

    protected $status;
    protected $documentId;
    protected $document;
    protected $created;

    public static function fromArray($array)
    {
        $res = new DocumentStatus();
        $res->documentId = $array["DOCUMENT_ID"];
        $res->status = $array["STATUS"];
        $res->created = $array["CREATED"];
        return $res;
    }

    static function create($doc, $value)
    {
        $status = TDataBaseDocument::getStatus($doc);
        if (!$status) {
            $status = new DocumentStatus();
            $status->documentId = $doc->getId();
            $status->document = $doc;
        }
        $status->status = $value;
        TDataBaseDocument::saveStatus($status);
        return $status;
    }

    function setValue($status)
    {
        $this->status = $status;
    }

    function getValue()
    {
        return $this->status;
    }

    function getCreated()
    {
        return $this->created;
    }

    function getDocumentId()
    {
        return $this->documentId;
    }

    function setDocumentId($docId)
    {
        $this->documentId = $docId;
        $this->document = null;
    }

    function getDocument()
    {
        if (!$this->document && !is_null($this->documentId)) {
            $this->document = TDataBaseDocument::getDocumentById($this->documentId);
        }
        return $this->document;
    }

    function setDocument($doc)
    {
        $this->document = $doc;
        $this->documentId = $doc->getId();
    }

    public function toArray()
    {

    }

    public function save()
    {
        TDataBaseDocument::saveStatus($this);
    }

}

