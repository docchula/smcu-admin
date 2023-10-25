@php
    use App\Models\Personnel;$list = Personnel::getYear($year);
@endphp
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Union Board</title>
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css"
          integrity="sha256-OweaP/Ic6rsV+lysfyS4h+LM6sRwuO3euTYfr6M124g=" crossorigin="anonymous"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <link href="https://static.keendev.net/font/chulalongkorn.css" rel="stylesheet"/>
    <style>
        body {
            background-color: #fafafa;
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            font-family: "Chulalongkorn", sans-serif;
        }

        main {
            flex: 1 0 auto;
            min-height: 300px;
            padding-top: 15px;
        }

        footer a {
            color: lightgrey;
        }

        footer a:hover {
            text-decoration: underline
        }

        header, main, .footer-copyright {
            padding-left: 360px !important;
            padding-right: 50px !important;
        }

        @media only screen and (max-width: 992px) {
            header, main, .footer-copyright {
                padding-left: 1.2rem !important;
                padding-right: 1.2rem !important;
            }
        }

        @media print {
            header, main, .footer-copyright {
                padding-left: 1.2rem !important;
                padding-right: 1.2rem !important;
            }

            #slide-out {
                display: none
            }

            .row .col.l3 {
                width: 25%;
                margin-left: auto;
                left: auto;
                right: auto
            }
        }

        .header {
            margin-top: 2.5rem;
        }

        .col {
            text-align: center;
            margin-bottom: 1rem
        }

        .col img {
            max-height: 15rem
        }
    </style>
</head>
<body>
<ul id="slide-out" class="sidenav sidenav-fixed">
    <li>
        <div class="user-view th">
            <img style="width: 4rem" src="https://docchula.com/assets/smcu.png"/>
        </div>
    </li>
    @foreach(Personnel::getYearList() as $y)
        <li class="{{ ($y == $year) ? 'active' : '' }}"><a href="/board/{{ $y }}">Board, Year {{ $y-543 }}-{{ $y-542 }}</a></li>
    @endforeach
    <li><a href="https://docchula.com/">Home</a></li>
</ul>

<nav class="top-nav green" style="height:100px">
    <header>
        <a href="#" data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a>
        <h4 class="header th">บุคลากร</h4>
    </header>
</nav>

<main class="th">
    <h4 style="font-weight:bold;text-align: center">คณะกรรมการสโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย ปีการศึกษา {{ $year }}</h4>
    <h5 style="text-align:center">The Board of Student Union, Academic Year {{ $year-543 }}-{{ $year-542 }}</h5>
    <div id="person-space">
        @foreach($list->chunk(4) as $personChunk)
            <div class="row">
                @foreach($personChunk as $personnel)
                    <div class="col s12 m6 l3">
                        @if($personnel->photo_path)
                            <img class="responsive-img" src="{{ Storage::url($personnel->photo_path) }}"><br>
                        @endif
                        <h5>{{ $personnel->name }}</h5>
                        <h6>{{ $personnel->position }}</h6>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</main>

<footer class="page-footer green">
    <div class="footer-copyright">
        <a href="https://docchula.com">สโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย</a>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"
        integrity="sha256-U/cHDMTIHCeMcvehBv1xQ052bPSbJtbuiw4QA9cTKz0=" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var instances = M.Sidenav.init(document.querySelectorAll('.sidenav'), {});
    });
</script>
</body>
</html>
