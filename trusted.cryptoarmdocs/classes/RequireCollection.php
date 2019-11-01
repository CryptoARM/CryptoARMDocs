<?php

namespace Trusted\CryptoARM\Docs;

/**
 * Represents multiple properties of the document.
 *
 * @see Collection
 */
class RequireCollection extends Collection {

    function items($i) {
        return parent::items($i);
    }

    function getSignStatusByUser($userId) {
        $list = $this->getList();
        $res = null;
        foreach ($list as $item) {
            if ($item->getUserId() != $userId) {
                continue;
            } else {
                $res = $item->getSignStatus();
            }
        }
        return $res;
    }

    function getEmailStatusByUser($userId) {
        $list = $this->getList();
        $res = null;
        foreach ($list as $item) {
            if ($item->getUserId() != $userId) {
                continue;
            } else {
                $res = $item->getEmailStatus();
            }
        }
        return $res;
    }

    function getUserList() {
        $list = $this->getList();
        $usersId = [];
        foreach ($list as $item) {
            $usersId[] = $item->getUserId();
        }
        $usersId = array_unique($usersId);
        return $usersId;
    }
}

