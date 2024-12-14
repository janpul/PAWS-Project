using System;
using System.Collections.Generic;
using System.Data.Entity.ModelConfiguration;
using System.Linq;
using System.Web;
using System.Web.Mvc;

namespace PAWSProject.Models
{
    public class tbluserMap : EntityTypeConfiguration<tbluserModel>
    {
        public tbluserMap()
        {
            HasKey(x => x.userID);
            ToTable("tbluser");
        }
    }
}