<div class="row">
    @php
        $requestedInformation = isset($requestedInformation) ? $requestedInformation : $application->requestedDocuments;
    @endphp
    @foreach($requestedInformation as $document)
        <div class="mail-box-letter-item">
            <div class="mail-box-letter-item-in">
                <div class="mail-box-letter-item-header tbl">
                    <div class="tbl-row">
                        <div class="tbl-cell tbl-cell-name">
                            {{--Request--}}
                            <span class="text-default">{{ $document->request }}</span>
                        </div>
                        @if (!isset($isEditable) || $isEditable)
                            <div class="tbl-cell tbl-cell-date">
                                @if(empty($document->response) && $authUser->isBorrower())
                                    <a class="btn btn-sm btn-success respond-button pull-right" data-toggle="modal"
                                       data-target="#reply-request-info-modal" data-document-id="{{ $document->id }}">
                                        <i class="fa fa-reply"></i> Respond
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mail-box-letter-item-txt">
                    {{--Response--}}
                    @if($document->response)
                        <span class="form-label">
                        Answer: <span class="text-default">{{ $document->response }}</span>
                    </span>
                        @if($document->files->count())
                            @foreach($document->files as $file)
                                <span class="form-label">
                            File: <a href="{{ $file->getPathRelativeToBucket() }}" target="_blank"
                                     class="text-success"> {{ $file->original_filename }}</a>
                        </span>
                            @endforeach
                        @endif
                    @else
                        <span class="form-label">
                        Answer: <span class="text-danger">Pending*</span>
                    </span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
