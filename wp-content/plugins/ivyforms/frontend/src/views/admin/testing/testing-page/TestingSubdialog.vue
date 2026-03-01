testing sub dialog:
<template>
  <div>
    <h1>Dialog Testing Page</h1>
    <div>
      Deafult Notification dialog:
      <IvyButtonAction
        :priority="'primary'"
        :size="'d'"
        :type="'fill'"
        @click="showDefaultDialog1 = true"
        >Open left
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'primary'"
        :size="'d'"
        :type="'fill'"
        @click="showDefaultDialog2 = true"
        >Open center
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'primary'"
        :size="'d'"
        :type="'fill'"
        @click="showDefaultDialog3 = true"
        >Open right
      </IvyButtonAction>
    </div>
    <div>
      Coming Soon Notification dialog:
      <IvyButtonAction
        :priority="'secondary'"
        :size="'d'"
        :type="'border'"
        @click="showUpComingDialog1 = true"
        >Open left
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'secondary'"
        :size="'d'"
        :type="'border'"
        @click="showUpComingDialog2 = true"
        >Open center
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'secondary'"
        :size="'d'"
        :type="'border'"
        @click="showUpComingDialog3 = true"
        >Open right
      </IvyButtonAction>
    </div>
    <div>
      Pro Notification dialog (center align dialog):
      <IvyButtonAction
        :priority="'secondary'"
        :size="'d'"
        :type="'fill'"
        @click="showProDialog1 = true"
        >Open left
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'secondary'"
        :size="'d'"
        :type="'fill'"
        @click="showProDialog2 = true"
        >Open center
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'secondary'"
        :size="'d'"
        :type="'fill'"
        @click="showProDialog3 = true"
        >Open right
      </IvyButtonAction>
    </div>
    <hr />
    <div>
      Success Notification dialog:
      <IvyButtonAction
        :priority="'primary'"
        :size="'d'"
        :type="'fill'"
        @click="showSuccessDialog1 = true"
        >Open left
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'primary'"
        :size="'d'"
        :type="'fill'"
        @click="showSuccessDialog2 = true"
        >Open center
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'primary'"
        :size="'d'"
        :type="'fill'"
        @click="showSuccessDialog3 = true"
        >Open right
      </IvyButtonAction>
    </div>
    <div>
      Warning Notification dialog:
      <IvyButtonAction
        :priority="'warning'"
        :size="'d'"
        :type="'fill'"
        @click="showWarningDialog1 = true"
        >Open left
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'warning'"
        :size="'d'"
        :type="'fill'"
        @click="showWarningDialog2 = true"
        >Open center
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'warning'"
        :size="'d'"
        :type="'fill'"
        @click="showWarningDialog3 = true"
        >Open right
      </IvyButtonAction>
    </div>
    <div>
      Danger Notification dialog (center align dialog):
      <IvyButtonAction
        :priority="'danger'"
        :size="'d'"
        :type="'fill'"
        @click="showDangerDialog1 = true"
        >Open left
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'danger'"
        :size="'d'"
        :type="'fill'"
        @click="showDangerDialog2 = true"
        >Open center
      </IvyButtonAction>
      <IvyButtonAction
        :priority="'danger'"
        :size="'d'"
        :type="'fill'"
        @click="showDangerDialog3 = true"
        >Open right
      </IvyButtonAction>
    </div>

    <IvyShortDialog
      v-model="showDefaultDialog1"
      title="Default Notification"
      :type="'default'"
      :align="'left'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'primary'"
          :size="'d'"
          :type="'fill'"
          @click="showDefaultDialog1 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showDefaultDialog1 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showDefaultDialog2"
      title="Default Notification"
      :type="'default'"
      :align="'center'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'primary'"
          :size="'d'"
          :type="'fill'"
          @click="showDefaultDialog2 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showDefaultDialog2 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showDefaultDialog3"
      title="Default Notification"
      :type="'default'"
      :align="'right'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'primary'"
          :size="'d'"
          :type="'fill'"
          @click="showDefaultDialog3 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showDefaultDialog3 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showUpComingDialog1"
      title="Coming Soon Feature"
      :type="'upcoming'"
      :align="'left'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'secondary'"
          :size="'d'"
          :type="'border'"
          @click="showUpComingDialog1 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showUpComingDialog1 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showUpComingDialog2"
      title="Coming Soon Feature"
      :type="'upcoming'"
      :align="'center'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'secondary'"
          :size="'d'"
          :type="'border'"
          @click="showUpComingDialog2 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showUpComingDialog2 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showUpComingDialog3"
      title="Coming Soon Feature"
      :type="'upcoming'"
      :align="'right'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'secondary'"
          :size="'d'"
          :type="'border'"
          @click="showUpComingDialog3 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showUpComingDialog3 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showProDialog1"
      title="Pro Feature"
      :type="'pro'"
      :align="'left'"
      show-close
      align-center
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'secondary'"
          :size="'d'"
          :type="'fill'"
          @click="showProDialog1 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showProDialog1 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showProDialog2"
      title="Pro Feature"
      :type="'pro'"
      :align="'center'"
      show-close
      align-center
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'secondary'"
          :size="'d'"
          :type="'fill'"
          @click="showProDialog2 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showProDialog2 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showProDialog3"
      title="Pro Feature"
      :type="'pro'"
      :align="'right'"
      show-close
      align-center
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'secondary'"
          :size="'d'"
          :type="'fill'"
          @click="showProDialog3 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showProDialog3 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showSuccessDialog1"
      title="Success Notification"
      :type="'success'"
      :align="'left'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'primary'"
          :size="'d'"
          :type="'fill'"
          @click="showSuccessDialog1 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showSuccessDialog1 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showSuccessDialog2"
      title="Success Notification"
      :type="'success'"
      :align="'center'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'primary'"
          :size="'d'"
          :type="'fill'"
          @click="showSuccessDialog2 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showSuccessDialog2 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showSuccessDialog3"
      title="Success Notification"
      :type="'success'"
      :align="'right'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'primary'"
          :size="'d'"
          :type="'fill'"
          @click="showSuccessDialog3 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showSuccessDialog3 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showWarningDialog1"
      title="Warning Notification"
      :type="'warning'"
      :align="'left'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'warning'"
          :size="'d'"
          :type="'fill'"
          @click="showWarningDialog1 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showWarningDialog1 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showWarningDialog2"
      title="Warning Notification"
      :type="'warning'"
      :align="'center'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'warning'"
          :size="'d'"
          :type="'fill'"
          @click="showWarningDialog2 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showWarningDialog2 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showWarningDialog3"
      title="Warning Notification"
      :type="'warning'"
      :align="'right'"
      show-close
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'warning'"
          :size="'d'"
          :type="'fill'"
          @click="showWarningDialog3 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showWarningDialog3 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showDangerDialog1"
      title="Danger Notification"
      :type="'error'"
      :align="'left'"
      show-close
      align-center
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'danger'"
          :size="'d'"
          :type="'fill'"
          @click="showDangerDialog1 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showDangerDialog1 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showDangerDialog2"
      title="Danger Notification"
      :type="'error'"
      :align="'center'"
      show-close
      align-center
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'danger'"
          :size="'d'"
          :type="'fill'"
          @click="showDangerDialog2 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showDangerDialog2 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>

    <IvyShortDialog
      v-model="showDangerDialog3"
      title="Danger Notification"
      :type="'error'"
      :align="'right'"
      show-close
      align-center
    >
      <div>Check out the all new dashboard view. Pages and exports now load faster.</div>
      <template #footer>
        <IvyButtonAction
          :priority="'danger'"
          :size="'d'"
          :type="'fill'"
          @click="showDangerDialog3 = false"
        >
          Action
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showDangerDialog3 = false"
        >
          Cancel
        </IvyButtonAction>
      </template>
    </IvyShortDialog>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyShortDialog from '@/views/_components/sub-dialog/IvyShortDialog.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'

const showDefaultDialog1 = ref(false)
const showDefaultDialog2 = ref(false)
const showDefaultDialog3 = ref(false)
const showUpComingDialog1 = ref(false)
const showUpComingDialog2 = ref(false)
const showUpComingDialog3 = ref(false)
const showProDialog1 = ref(false)
const showProDialog2 = ref(false)
const showProDialog3 = ref(false)
const showSuccessDialog1 = ref(false)
const showSuccessDialog2 = ref(false)
const showSuccessDialog3 = ref(false)
const showWarningDialog1 = ref(false)
const showWarningDialog2 = ref(false)
const showWarningDialog3 = ref(false)
const showDangerDialog1 = ref(false)
const showDangerDialog2 = ref(false)
const showDangerDialog3 = ref(false)
</script>

<style scoped>
h1 {
  font-size: 24px;
  margin-bottom: 16px;
}

button {
  margin-top: 16px;
  margin-right: 8px;
}

.flex-right {
  flex: 2;
}
</style>
