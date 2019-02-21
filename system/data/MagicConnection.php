<?php
    namespace data;
    class MagicConnection extends DbConnection
    {
        public function conn($db)
        {
            return parent::connection($db);
        }
    }