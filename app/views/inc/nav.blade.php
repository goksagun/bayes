<div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/">Bayes</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
            <li {{ Request::is('/') ? 'class="active"' : '' }}><a href="/">Home <span class="sr-only">(current)</span></a></li>
            <li {{ Request::is('faker') ? 'class="active"' : '' }}><a href="/faker">Faker</a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Reviews <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <li {{ Request::is('reviews') ? 'class="active"' : '' }}><a href="/reviews">Reviews</a></li>
                    <li class="divider"></li>
                    <li><a href="/reviews/seed">Reviews Seed</a></li>
                    <li class="divider"></li>
                    <li {{ Request::is('reviews/truncate') ? 'class="active"' : '' }}><a href="/reviews/truncate">Truncate Reviews</a></li>
                </ul>
            </li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="#">Link</a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Dropdown <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                </ul>
            </li>
        </ul>
    </div><!-- /.navbar-collapse -->
</div><!-- /.container-fluid -->