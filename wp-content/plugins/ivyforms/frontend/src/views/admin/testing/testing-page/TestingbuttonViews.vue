<template>
  <!--  fill -->
  <div class="button-wrapper">
    <h1>Fill</h1>
    <!--  Success-->
    <h2>Success</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" icon-start="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" icon-end="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  Warning-->
    <h2>Warning</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" icon-start="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" icon-end="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  Danger-->
    <h2>Danger</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" icon-start="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" icon-end="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>

    <!--  Primary -->
    <h2>Primary</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" icon-start="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" icon-end="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  Secondary -->
    <h2>Secondary</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" icon-start="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" icon-end="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  tertiary -->
    <h2>tertiary</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" icon-start="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" icon-end="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!-- white/black-->
    <h2>White</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" icon-start="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" icon-end="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!-- shadow-white-->
    <h2>shadow-White</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" icon-start="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" icon-end="plus">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
  </div>
  <!--  border -->
  <div class="button-wrapper">
    <h1>Border</h1>
    <!--  Success-->
    <h2>Success</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" type="border">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction
          priority="success"
          size="l"
          icon-start="plus"
          icon-start-type="fill"
          type="border"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" icon-end="plus" type="border"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" icon-only icon-start="plus" type="border"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" disabled type="border">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" icon-start="loading" loading type="border"
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  Warning-->
    <h2>Warning</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" type="border">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" icon-start="plus" type="border"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" icon-end="plus" type="border"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" icon-only icon-start="plus" type="border"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" disabled type="border">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" icon-start="loading" loading type="border"
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  Danger-->
    <h2>Danger</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" type="border">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" icon-start="plus" type="border"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" icon-end="plus" type="border"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" type="border" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" type="border" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" type="border" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>

    <!--  Primary -->
    <h2>Primary</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="border">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="border" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="border" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="border" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="border" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="border" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  Secondary -->
    <h2>Secondary</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="border">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="border" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="border" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="border" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="border" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="border" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  tertiary -->
    <h2>tertiary</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" type="border">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" type="border" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" type="border" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" type="border" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" type="border" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" type="border" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!-- white/black-->
    <h2>White</h2>
    <el-row :gutter="20" style="padding: 10px">
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="border">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="border" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="border" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="border" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="border" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="border" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!-- shadow-white-->
    <h2>Shadow-white</h2>
    <el-row :gutter="20" style="padding: 10px">
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="border">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="border" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="border" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="border" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="border" disabled
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="border" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
  </div>
  <!--  ghost -->
  <div class="button-wrapper">
    <h1>Ghost</h1>
    <!--  Success-->
    <h2>Success</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" type="ghost">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" type="ghost" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" type="ghost" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" type="ghost" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" type="ghost" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="success" size="l" type="ghost" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  Warning-->
    <h2>Warning</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" type="ghost">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" type="ghost" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" type="ghost" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" type="ghost" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" type="ghost" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="warning" size="l" type="ghost" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  Danger-->
    <h2>Danger</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" type="ghost">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" type="ghost" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" type="ghost" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" type="ghost" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" type="ghost" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="danger" size="l" type="ghost" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>

    <!--  Primary -->
    <h2>Primary</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="ghost">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="ghost" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="ghost" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="ghost" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="ghost" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="primary" size="l" type="ghost" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  Secondary -->
    <h2>Secondary</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="ghost">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="ghost" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="ghost" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="ghost" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="ghost" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="secondary" size="l" type="ghost" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!--  tertiary -->
    <h2>tertiary</h2>
    <el-row :gutter="20">
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" type="ghost">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction
          priority="tertiary"
          type="ghost"
          icon-start="trash"
          icon-start-type="outline"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" type="ghost" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" type="ghost" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" type="ghost" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="tertiary" size="l" type="ghost" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <!-- white/black-->
    <h2>White</h2>
    <el-row :gutter="20" style="padding: 10px">
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="ghost">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="ghost" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="ghost" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="ghost" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="ghost" disabled>Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="white" size="l" type="ghost" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
    <h2>Shadow White</h2>
    <el-row :gutter="20" style="padding: 10px">
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="ghost">Form</IvyButtonAction>
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="ghost" icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="ghost" icon-end="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="ghost" icon-only icon-start="plus"
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="ghost" disabled
          >Form</IvyButtonAction
        >
      </el-col>
      <el-col :span="4">
        <IvyButtonAction priority="shadow-white" size="l" type="ghost" icon-start="loading" loading
          >Form</IvyButtonAction
        >
      </el-col>
    </el-row>
  </div>
</template>

<script setup lang="ts"></script>
<style lang="scss">
.el-row {
  margin-bottom: 20px;
}
.button-wrapper {
  padding: 20px;
  min-width: 1024px;
}
</style>
