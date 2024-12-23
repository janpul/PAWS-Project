using System;
using System.Collections.Generic;
using System.Data.Entity.ModelConfiguration;
using System.Linq;
using System.Web;
using System.Web.Mvc;

namespace PAWSProject.Models
{
    public class tbladoptformMap : EntityTypeConfiguration<tbladoptformModel>
    {
        public tbladoptformMap()
        {
            HasKey(x => x.formID);
            ToTable("tbladoptform");
        }
    }
}