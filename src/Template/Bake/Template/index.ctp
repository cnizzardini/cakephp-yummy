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
%>
<?php
/**
  * @var \<%= $namespace %>\View\AppView $this
  */
?>
<%
use Cake\Utility\Inflector;

$fields = collection($fields)
    ->filter(function($field) use ($schema) {
        return !in_array($schema->columnType($field), ['binary', 'text']);
    });

if (isset($modelObject) && $modelObject->behaviors()->has('Tree')) {
    $fields = $fields->reject(function ($field) {
        return $field === 'lft' || $field === 'rght';
    });
}

if (!empty($indexColumns)) {
    $fields = $fields->take($indexColumns);
}
%>

<?php
$this->start('sidebar');
?>
<li>
    <a href="<%= $pluarVar %>/add">
        <i class="ti-plus"></i>
        <p>Add <%= ucfirst($singularVar) %></p>
    </a>
</li>
<?php 
$this->end(); 
?>
<div class="<%= $pluralVar %> row">
	<div class="col-md-12">
		<div class="card">
			<div class="header">
				<h4 class="title"><?= __('<%= $pluralHumanName %>') ?></h4>
				<p class="category"><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}} &#8211 displaying {{current}} out of {{count}} records')]) ?></p>
			</div>
			<div class="content table-responsive table-full-width">
                <table class="table table-striped" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
            <% foreach ($fields as $field): %>
                <th scope="col"><?= $this->Paginator->sort('<%= $field %>') ?></th>
            <% endforeach; %>
                <th scope="col" class="actions">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($<%= $pluralVar %> as $<%= $singularVar %>): ?>
                        <tr>
        <%        foreach ($fields as $field) {
                    $isKey = false;
                    if (!empty($associations['BelongsTo'])) {
                        foreach ($associations['BelongsTo'] as $alias => $details) {
                            if ($field === $details['foreignKey']) {
                                $isKey = true;
        %>
                <td><?= $<%= $singularVar %>->has('<%= $details['property'] %>') ? $this->Html->link($<%= $singularVar %>-><%= $details['property'] %>-><%= $details['displayField'] %>, ['controller' => '<%= $details['controller'] %>', 'action' => 'view', $<%= $singularVar %>-><%= $details['property'] %>-><%= $details['primaryKey'][0] %>]) : '' ?></td>
        <%
                                break;
                            }
                        }
                    }
                    if ($isKey !== true) {
                        if (!in_array($schema->columnType($field), ['integer', 'biginteger', 'decimal', 'float'])) {
        %>
                <td><?= h($<%= $singularVar %>-><%= $field %>) ?></td>
        <%
                        } else {
        %>
                <td><?= $this->Number->format($<%= $singularVar %>-><%= $field %>) ?></td>
        <%
                        }
                    }
                }

                $pk = '$' . $singularVar . '->' . $primaryKey[0];
        %>
                        <td class="actions">
                                <?= $this->Html->link(__('Edit'), ['action' => 'edit', <%= $pk %>], ['class' => 'btn btn-info btn-block btn-fill']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="paginator">
            <ul class="pagination">
                <?= $this->Paginator->first('<< ' . __('first')) ?>
                <?= $this->Paginator->prev('< ' . __('previous')) ?>
                <?= $this->Paginator->numbers() ?>
                <?= $this->Paginator->next(__('next') . ' >') ?>
                <?= $this->Paginator->last(__('last') . ' >>') ?>
            </ul>
        </div>
    </div>
</div>
