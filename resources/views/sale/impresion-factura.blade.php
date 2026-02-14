<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" href="{{url('public/logo', $general_setting->site_logo)}}" />
        <title>{{$general_setting->site_title}}</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="all,follow">

        <style type="text/css">
            * {
                font-size: 14px;
                line-height: 18px;
                font-family: monospace;
                text-transform: capitalize;
            }
            .btn {
                padding: 7px 10px;
                text-decoration: none;
                border: none;
                display: block;
                text-align: center;
                margin: 7px;
                cursor:pointer;
            }

            .btn-info {
                background-color: #999;
                color: #FFF;
            }

            .btn-primary {
                background-color: #6449e7;
                color: #FFF;
                width: 100%;
            }

            table {
                border-collapse: collapse;
            }

            tr {
                border-bottom: 1px solid #ccc;
            }

            tr {border-bottom: 1px dotted #ddd;}
            td,th {padding: 2px 0;width: 10%;text-align: start;}

            table {width: 95%;}
            tfoot tr th:first-child {text-align: left;}

            .centered {
                text-align: center;
                align-content: center;
            }
            small{font-size:11px;}

            @media print {
                * {
                    font-size:12px;
                    line-height: 20px;
                }
                td,th {padding: 1px 0;}
                .hidden-print {
                    display: none !important;
                }
                @page { margin: 0; } body { margin: 0.5cm; margin-bottom:1.6cm; } 
            }

            .left {
                float: left;
                width: 50%;
                text-align: right;
                margin: 0px 5px;
                display: inline;
                text-align: start
                }
            .right {
                float: left;
                text-align: left;
                margin: 0px 5px;
                display: inline;
                width: 40%
            }
            .mb-1{
                margin-bottom: 4px
            }

        </style>
    </head>
    <body>

        <div style="max-width:90%;margin:0 auto">
            <div class="hidden-print">
                <table>
                    <tr>
                        <td><a href="{{ route('sale.pos')}}" class="btn btn-info"><i class="fa fa-arrow-left"></i> Volver POS</a> </td>
                    </tr>
                </table>
                <br>
            </div>
                
            <div id="receipt-data">
                <object>
                    <embed id="pdfID" type="text/html" width="90%" height="500" src="data:application/pdf;base64,{{$data['bytes']}}" />
                </object>                                
            </div>
        </div>

        <script type="text/javascript">
            
        </script>

    </body>
</html>

