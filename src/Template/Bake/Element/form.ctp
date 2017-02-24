<%
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Utility\Inflector;

$fields = collection($fields)
    ->filter(function($field) use ($schema) {
        return $schema->columnType($field) !== 'binary';
    });
%>
<?php
$this->assign('title', '<%= Inflector::humanize($singularVar) %>');
$this->start('sidebar');
//echo $this->element('Navigation/<%= Inflector::humanize($singularVar) %>.ctp');
$this->end(); 
?>
<div class="card">
	<div class="header">
		<h4 class="title"><%= Inflector::humanize($singularVar) %></h4>
	</div>
	<div class="content">
    <?php echo $this->Form->create($<%= $singularVar %>,['class'=>'form form-custom form-medium','role'=>'form']); ?>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
<%
        foreach ($fields as $field) {
            if (in_array($field, $primaryKey)) {
                continue;
            }
			
            if (isset($keyFields[$field])) {
                $fieldData = $schema->column($field);
                if (!empty($fieldData['null'])) {
%>
                    
            <div class="form-group">
                <?php echo $this->Form->control('<%= $field %>', [
                    'class' => 'form-control border-input',
                    'options' => $<%= $keyFields[$field] %>, 
                    'empty' => true
                ]); ?>
            </div>
<%
                } else {
%>
                    
                <div class="form-group">
                    <?php echo $this->Form->control('<%= $field %>', [
                        'class'=>'form-control border-input',
                        'options' => $<%= $keyFields[$field] %>
                ]); ?>
                </div>
    <%
                }
                continue;
            }
            if (!in_array($field, ['created', 'modified', 'updated'])) {
%>
                
                <div class="form-group">
                    <?php echo $this->Form->control('<%= $field %>',[
                        'class'=>'form-control border-input',
                        'placeholder'=>'<%= $field %>'
                    ]); ?>
                </div>
    <%
            }
        }
%>
<%
        if (!empty($associations['BelongsToMany'])) {
            foreach ($associations['BelongsToMany'] as $assocName => $assocData) {
%>
                
                <div class="form-group">
                    <?php echo $this->Form->input('<%= $assocData['property'] %>._ids', [
                        'class'=>'form-control border-input',
                        'options' => $<%= $assocData['variable'] %>
                ]); ?>
                </div>
<%
            }
        }
%>      
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="text-center">
                    <?php echo $this->Form->button(__('Submit'),['class'=>'btn btn-info btn-fill btn-wd']) ?>
                </div>
            </div>
        </div>
    <?php echo $this->Form->end() ?>
    </div>
</div>
