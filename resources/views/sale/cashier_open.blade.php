@extends('layout.main')
@section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>{{trans('file.Add Cashier')}}</h4>
                    </div>
                    <div class="card-body">
                        <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                        {!! Form::open(['route' => 'cashier.store', 'method' => 'post', 'id' => 'cashier-form']) !!}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans('file.Biller')}} *</label>
                                            @if($biller_data)
                                            <input type="hidden" name="biller_id_hidden" value="{{$biller_data->id}}">
                                            @endif
                                            <select required id="biller_id" name="biller_id" class="selectpicker form-control" 
                                            data-live-search="true" data-live-search-style="begins" title="Seleccione Facturador..." onchange="oldMonto();">
                                                @foreach($lims_biller_list as $biller)
                                                <option value="{{$biller->id}}">{{$biller->name}} [{{$biller->punto_venta_siat}}]</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Saldo Anterior</label>
                                            <input id="amount_old" type="number" name="amount_old" step="any" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Monto Apertura *</label>
                                            <input id="amount_id" required type="number" name="amount_start" step="any" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        {!! Form::close() !!}
                        <div class="form-group">
                            <input id="btn_save" value="{{trans('file.submit')}}" class="btn btn-primary" onclick="validarForm();">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>  
    
    <!-- account modal -->
    <div id="ajustement-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Adjustment')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                  <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                  <form id="form_adjustment" method="POST">
                      <div class="form-group">
                        <label>{{trans('file.Account')}} *</label>
                        <input type="hidden" name="account_id">
                        <input type="hidden" name="ajax" value="true">
                        <input type="text" name="name_account" class="form-control" readonly>
                      </div>
                      <div class="form-group">
                        <label>{{trans('file.Type Adjustment')}} *</label>
                        <select required id="type_adjustment_id" name="type_adjustment" class="selectpicker form-control" title="Seleccione un Ajuste...">
                            <option value="ING">Ingreso</option>
                            <option value="EGR">Egreso</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>{{trans('file.Amount')}} *</label>
                        <input required type="number" name="amount" step="any" class="form-control">
                      </div>
                      <div class="form-group">
                          <label>{{trans('file.Note')}} *</label>
                          <textarea required name="note" rows="3" class="form-control"></textarea>
                      </div>
                      <div class="form-group">
                          <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                      </div>
                  </form>
                </div>
            </div>
        </div>
      </div>
</section>
<script type="text/javascript">
var btn = document.getElementById("btn_save");
btn.disabled = false;
$('select[name=biller_id]').val($("input[name='biller_id_hidden']").val());
$('.selectpicker').selectpicker('refresh');
oldMonto();
$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
});

$(document).ready(function() {              
        $(document).on('submit', '#form_adjustment', function() {   
            //Obtenemos datos.      
            var data = $(this).serialize();
            $.ajax({            
                type : 'POST',
                url  : 'adjustment_account/save',
                data : data,
                success :  function(data) {    
                    console.log("result: "+data)  
                    $('#ajustement-modal').modal('hide')
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                    btn.disabled = false;
                    if(data){
                    alert("Ajuste Guardado Continue. Registre su apertura de Caja");
                    document.getElementById("cashier-form").submit();
                    }else
                    alert("Error al Guardar Ajuste intente nuevamente");

                }
            });         
                return false;           
        });        
});

function oldMonto(){ 
    var id = $('select[name=biller_id]').val();
    $.ajax({
        type:'GET',
        url:'cashier/amountold/' + id,
        success:function(data){
            console.log(data);
            $('input[name="amount_old"]').val(data.amount_end);
            $('input[name="amount_start"]').val(data.amount_end);
        }
    });
}

function validarForm(){ 
    var id = $('select[name=biller_id]').val();
    var amount = document.getElementById("amount_id").value;
    console.log("amount: " + amount);
    $.ajax({
        type:'GET',
        url:'cashier/verified/' + id,
        success:function(data){
            console.log(data);
            if(data['cashier'] != null){
                if(data['cashier'].amount_end == amount){
                    btn.disabled = false;
                    document.getElementById("cashier-form").submit();
                }else{
                    console.log("Montos no igual al de cuenta");
                    btn.disabled = true;
                    confirm = confirm('Monto no coincide con caja seleccionada. ¿Desea Hacer Ajuste?');
                    if(confirm == true){
                        //openDialogNew();
                        $('input[name="account_id"]').val(data['account'].id);
                        $('input[name="name_account"]').val(data['account'].name + "["+data['account'].account_no+"]");
                        $('#ajustement-modal').modal();
                    }else{
                        alert('Los datos no han sido actualizados');
                        btn.disabled = true;
                        return false;
                    }
                }
            }else{
                document.getElementById("cashier-form").submit();
            }
        }
    });

    function openDialogNew(){
        var url = "accounts/list"
        $('#biller_id').empty();
        $.get(url, function(data) {
            $("#biller_id :selected").val();
        });
      }
      // Rutina para agregar opciones a un <select>
    function addOptions(domElement, array) {
          var select = document.getElementById(domElement);

          for (value in array) {
              var option = document.createElement("option");
              option.text = array[value].name + " [" + array[value].account_no + "]";
              option.value = array[value].id;
              select.add(option);
          }

    }
}
</script>
@endsection