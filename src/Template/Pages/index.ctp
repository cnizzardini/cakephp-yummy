<?php
$this->set('title', 'Yummy - Delightfully tasty tools for your CakePHP project | CAKEPHP-YUMMY');
$this->set('description', 'Delightfully tasty tools for your CakePHP project');
?>
<div class="col-md-12">
    <div class="card">
        <div class="header">
            <h4 class="title">Yummy Demo!</h4>
            <p class="category">Yummy is a set of delightfully tasty components, helpers, and bake scripts for your CakePHP 3 project. </p>
        </div>
        <div class="content">
            <h2>About</h2>
            <p>
                For help please checkout the <a href="https://github.com/cnizzardini/cakephp-yummy">cakephp-yummy</a> github project. You'll find the README, wiki, and can report any 
                issues you find.
            </p>
            <p>
                These demo pages will not display in your application when debug is set to false, they will instead throw a 403 forbidden exception.
            </p>
            
            <h2>Yummy Bake Templates</h2>
            <p>
                You will want to download <a href="https://www.creative-tim.com/product/paper-dashboard">Paper Dashboard</a> by Creative Tim and include in your project. You 
                can use the src in here to help guide you through the process. Yummy Bake will bake paper dashboard styled template files for your cake project. 
                To use, simply run the bake command from your application directory:</p>
            <blockquote>
                <p class="text-info">
                    bin/cake bake template {ControllerName} -t Yummy
                </p>
            </blockquote>
            
            <p>Be sure to check Creative Tim <a href="https://www.creative-tim.com/license">licensing requirements</a> before using the Paper Dashboard theme.</p>
        </div>
    </div>
</div>