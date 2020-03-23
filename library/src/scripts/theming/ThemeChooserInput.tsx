/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { IComboBoxOption } from "@library/features/search/SearchBar";
import SelectOne, { IMenuPlacement, MenuPlacement } from "@library/forms/select/SelectOne";
import { t } from "@vanilla/i18n";
import classNames from "classnames";
import React, { useEffect, useMemo, useState } from "react";
import { themeDropDownClasses } from "@library/forms/themeEditor/ThemeDropDown.styles";
import { ThemeType, useThemeActions } from "@library/theming/ThemeActions";
import { IThemeInfo } from "@library/theming/CurrentThemeInfo";
import { useThemeSettingsState } from "@library/theming/themeSettingsReducer";
import { LoadStatus } from "@library/@types/api/core";
import Loader from "@library/loaders/Loader";
import { DashboardLabelType } from "@dashboard/forms/DashboardFormLabel";
import { useFormGroup } from "@dashboard/forms/DashboardFormGroup";
import { useSubcommunities } from "@subcommunities/subcommunities/subcommunitySelectors";
import { DashboardSelect } from "@dashboard/forms/DashboardSelect";

interface IProps extends IMenuPlacement {
    value?: string | null;
    initialValue: string | null;
    onChange?: (value: string | null) => void;
}

const themeGroupOptions = () => {
    const themeSettingsState = useThemeSettingsState();
    const actions = useThemeActions();
    if (themeSettingsState.themes.status === LoadStatus.PENDING) {
        actions.getAllThemes();
    }
    if (!themeSettingsState.themes.data || themeSettingsState.themes.status === LoadStatus.LOADING) {
        return <Loader />;
    }

    const { templates, themes } = themeSettingsState.themes.data;
    let dbThemeGroupOptions: IComboBoxOption[] = [];
    let templateThemeGroupOptions: IComboBoxOption[] = [];

    dbThemeGroupOptions = themes.map(function(theme, index) {
        return {
            value: theme.themeID,
            label: theme.name,
        };
    });

    templateThemeGroupOptions = templates.map(function(template, index) {
        return {
            value: template.themeID,
            label: template.name,
        };
    });

    return [...dbThemeGroupOptions, ...templateThemeGroupOptions] as IComboBoxOption[];
};
export function ThemeChooserInput(props: IProps) {
    const [ownValue, setOwnValue] = useState(props.initialValue);
    const setValue = props.onChange ?? setOwnValue;

    const currentValue = props.value ?? ownValue;
    let options;

    useEffect(() => {
        options = themeGroupOptions();
        if (ownValue === null) {
            options.find(option => {
                if (option.value === props.initialValue?.toString()) {
                    setOwnValue(option.value);
                }
            });
        }
    });

    return (
        <>
            <DashboardSelect
                options={options}
                onChange={newValue => {
                    setValue(newValue?.value ? newValue.value.toString() : null);
                }}
                value={themeGroupOptions[currentValue ? currentValue.toString() : 0]}
            />
        </>
    );
}
