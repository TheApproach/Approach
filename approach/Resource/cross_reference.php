<?php

namespace Approach\Resource;

enum cross_reference
{
    case location_location;
    case location_field;
    case location_feature;
    case location_quality;
    case location_quantity;
    case location_mode;
    case location_type;
    case location_map;
    case location_authority;
    case location_status;
    case location_state;
    case location_assigned;

    case operation_location;
    case operation_field;
    case operation_feature;
    case operation_quality;
    case operation_quantity;
    case operation_mode;
    case operation_type;
    case operation_map;
    case operation_authority;
    case operation_status;
    case operation_state;
    case operation_assigned;

    case field_location;
    case field_field;
    case field_feature;
    case field_quality;
    case field_quantity;
    case field_mode;
    case field_type;
    case field_map;
	case field_authority;
	case field_status;
	case field_state;
	case field_assigned;
	
    case feature_location;
    case feature_field;
    case feature_feature;
    case feature_quality;
    case feature_quantity;
    case feature_mode;
    case feature_type;
    case feature_map;
    case feature_authority;
    case feature_status;
    case feature_state;
    case feature_assigned;

    case quality_location;
    case quality_field;
    case quality_feature;
    case quality_quality;
    case quality_quantity;
    case quality_mode;
    case quality_type;
    case quality_map;
    case quality_authority;
    case quality_status;
    case quality_state;
    case quality_assigned;

    case quantity_location;
    case quantity_field;
    case quantity_feature;
    case quantity_quality;
    case quantity_quantity;
    case quantity_mode;
    case quantity_type;
    case quantity_map;
    case quantity_authority;
    case quantity_status;
    case quantity_state;
    case quantity_assigned;

    case mode_location;
    case mode_field;
    case mode_feature;
    case mode_quality;
    case mode_quantity;
    case mode_mode;
    case mode_type;
    case mode_map;
    case mode_authority;
    case mode_status;
    case mode_state;
    case mode_assigned;

    case type_location;
    case type_field;
    case type_feature;
    case type_quality;
    case type_quantity;
    case type_mode;
    case type_type;
    case type_map;
    case type_authority;
    case type_status;
    case type_state;
    case type_assigned;

    case map_location;
    case map_field;
    case map_feature;
    case map_quality;
    case map_quantity;
    case map_mode;
    case map_type;
    case map_map;
    case map_authority;
    case map_status;
    case map_state;
    case map_assigned;

    case authority_location;
    case authority_field;
    case authority_feature;
    case authority_quality;
    case authority_quantity;
    case authority_mode;
    case authority_type;
    case authority_map;
    case authority_authority;
    case authority_status;
    case authority_state;
    case authority_assigned;

    case status_location;
    case status_field;
    case status_feature;
    case status_quality;
    case status_quantity;
    case status_mode;
    case status_type;
    case status_map;
    case status_authority;
    case status_status;
    case status_state;
    case status_assigned;

    case state_location;
    case state_field;
    case state_feature;
    case state_quality;
    case state_quantity;
    case state_mode;
    case state_type;
    case state_map;
    case state_authority;
    case state_status;
    case state_state;

    case assigned_location;
    case assigned_field;
    case assigned_feature;
    case assigned_quality;
    case assigned_quantity;
    case assigned_mode;
    case assigned_type;
    case assigned_map;
    case assigned_authority;
    case assigned_status;
    case assigned_state;
}
