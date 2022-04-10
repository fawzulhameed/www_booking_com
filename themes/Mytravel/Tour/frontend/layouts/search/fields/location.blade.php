<div class="item">
	<span class="d-block text-gray-1  font-weight-normal mb-0 text-left">
		{{ $field['title'] ?? "" }}
	</span>
	<div class="mb-4">
		<div class="input-group border-bottom border-width-2 border-color-1 py-2">
			<i class="flaticon-pin-1 d-flex align-items-center mr-2 text-primary font-weight-semi-bold font-size-22"></i>
			<?php
			$location_name = "";
			$list_json = [];
			$traverse = function ($locations, $prefix = '') use (&$traverse, &$list_json, &$location_name) {
				foreach ($locations as $location) {
					$translate = $location->translateOrOrigin(app()->getLocale());
					if (Request::query('location_id') == $location->id) {
						$location_name = $translate->name;
					}
					$list_json[] = [
							'id'    => $location->id,
							'title' => $prefix.' '.$translate->name,
					];
					$traverse($location->children, $prefix.'-');
				}
			};
			$traverse($tour_location);
			?>
			<div class="smart-search border-0 p-0 form-control  height-40">
				<input type="text" class="smart-search-location parent_text  font-weight-bold font-size-16 shadow-none hero-form font-weight-bold border-0 p-0" {{ ( empty(setting_item("tour_location_search_style")) or setting_item("tour_location_search_style") == "normal" ) ? "readonly" : ""  }} placeholder="{{__("Where are you going?")}}" value="{{ $location_name }}" data-onLoad="{{__("Loading...")}}"
					   data-default="{{ json_encode($list_json) }}">
				<input type="hidden" class="child_id" name="location_id" value="{{Request::query('location_id')}}">
			</div>
		</div>
	</div>
	<!-- End Input -->
</div>