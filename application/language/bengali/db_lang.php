<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'Unable to determine the database settings based on the connection string you submitted.';
$lang['db_unable_to_connect'] = 'Unable to connect to database server using the settings providedUnable to connect to database server using the settings provided.';
$lang['db_unable_to_select'] = 'You can not select the specified database: %s';
$lang['db_unable_to_create'] = 'Could not create the database: %s';
$lang['db_invalid_query'] = 'The query you sent is invalid.';
$lang['db_must_set_table'] = 'You must set the database table to use with the query.';
$lang['db_must_use_set'] = 'You must use the "set" method to update an item.';
$lang['db_must_use_index'] = 'You must specify an index to match on for batch updates.';
$lang['db_batch_missing_index'] = 'One or more rows submitted for batch update is missing the index specified.';
$lang['db_must_use_where'] = 'Updates are not allowed unless they contain a "where" clause.';
$lang['db_del_must_use_where'] = 'Deletes are not allowed unless they contain a "where" or "like" clause.';
$lang['db_field_param_missing'] = 'To get fields requires the table name as a parameter.';
$lang['db_unsupported_function'] = 'This feature is not available for the database you are using.';
$lang['db_transaction_failure'] = 'Transaction failure: rollback performed.';
$lang['db_unable_to_drop'] = 'Can not delete the specified database.';
$lang['db_unsupported_feature'] = 'unsupported feature of the database platform in use.';
$lang['db_unsupported_compression'] = 'The file compression format you chose is not supported by the server.';
$lang['db_filepath_error'] = 'Can not write data to the file path you have submitted.';
$lang['db_invalid_cache_path'] = 'The location of the cache you submitted is not valid or writable.';
$lang['db_table_name_required'] = 'You need a table name for this operation.';
$lang['db_column_name_required'] = 'You need a column name for this operation.';
$lang['db_column_definition_required'] = 'A column definition is required for that operation.';
$lang['db_unable_to_set_charset'] = 'Unable to set character set of connecting client: %s';
$lang['db_error_heading'] = 'It was an error in the database';
